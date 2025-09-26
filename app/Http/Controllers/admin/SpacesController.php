<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\SpaceMaintenance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Exception;

class SpacesController extends Controller
{
    /**
     * Display the spaces management page
     */
    public function index(Request $request): View
    {
        // Get filter parameters
        $filters = [
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'floor' => $request->get('floor'),
        ];

        // Get spaces with filters and relationships
        $spaces = $this->getFilteredSpaces($filters);
        
        // Get statistics
        $stats = $this->getSpacesStatistics();
        
        // Get maintenance schedule
        $maintenanceSchedule = $this->getMaintenanceSchedule();
        
        // Update space statuses
        $this->updateSpaceStatuses();

        return view('admin.space', compact('spaces', 'stats', 'maintenanceSchedule', 'filters'));
    }

    /**
     * Get filtered spaces list
     */
    private function getFilteredSpaces(array $filters)
    {
        $query = Space::with([
            'currentReservationRelation.user', 
            'upcomingReservationsRelation.user', 
            'activeMaintenanceRelation'
        ])
            ->where('is_active', true)
            ->orderBy('floor_level')
            ->orderBy('number');

        // Apply filters
        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['floor'])) {
            $query->byFloor($filters['floor']);
        }

        return $query->paginate(20);
    }

    /**
     * Get spaces statistics
     */
    private function getSpacesStatistics(): array
    {
        $totalSpaces = Space::where('is_active', true)->count();
        $availableSpaces = Space::where('status', Space::STATUS_AVAILABLE)->where('is_active', true)->count();
        $occupiedSpaces = Space::where('status', Space::STATUS_OCCUPIED)->count();
        $reservedSpaces = Space::where('status', Space::STATUS_RESERVED)->count();
        $maintenanceSpaces = Space::where('status', Space::STATUS_MAINTENANCE)->count();

        // Calculate overall occupancy rate
        $occupancyRate = $totalSpaces > 0 ? round((($occupiedSpaces + $reservedSpaces) / $totalSpaces) * 100, 1) : 0;

        // Today's reservations
        $todayReservations = SpaceReservation::today()
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->count();

        // Yesterday's reservations for comparison - Fixed
        $yesterdayReservations = SpaceReservation::whereDate('starts_at', today()->subDay())
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->count();

        // Pending maintenance
        $pendingMaintenance = SpaceMaintenance::whereIn('status', ['scheduled', 'in_progress'])->count();
        $overdueMaintenance = SpaceMaintenance::overdue()->count();
        $urgentMaintenance = SpaceMaintenance::getUrgentCount();

        // Calculate trends
        $reservationsTrend = $yesterdayReservations > 0 
            ? round((($todayReservations - $yesterdayReservations) / $yesterdayReservations) * 100, 1)
            : 0;

        // Revenue calculations
        $todayRevenue = SpaceReservation::whereDate('starts_at', today())
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->sum('total_cost') ?? 0;

        return [
            'total_spaces' => $totalSpaces,
            'available_spaces' => $availableSpaces,
            'occupied_spaces' => $occupiedSpaces,
            'reserved_spaces' => $reservedSpaces,
            'maintenance_spaces' => $maintenanceSpaces,
            'occupancy_rate' => $occupancyRate,
            'today_reservations' => $todayReservations,
            'yesterday_reservations' => $yesterdayReservations,
            'reservations_trend' => $reservationsTrend,
            'pending_maintenance' => $pendingMaintenance,
            'overdue_maintenance' => $overdueMaintenance,
            'urgent_maintenance' => $urgentMaintenance,
            'today_revenue' => $todayRevenue,
        ];
    }

    /**
     * Get maintenance schedule
     */
    private function getMaintenanceSchedule()
    {
        return SpaceMaintenance::with(['space'])
            ->whereIn('status', ['scheduled', 'in_progress', 'postponed'])
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();
    }

    /**
     * Update space statuses based on current reservations
     */
    private function updateSpaceStatuses(): void
    {
        // Auto-complete expired reservations
        SpaceReservation::autoCompleteExpired();

        // Update all space statuses
        Space::chunk(50, function ($spaces) {
            foreach ($spaces as $space) {
                $space->updateStatus();
            }
        });
    }

    /**
     * Store a new space
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'number' => 'required|string|max:50|unique:spaces,number',
                'type' => ['required', Rule::in(array_keys(Space::getAvailableTypes()))],
                'description' => 'nullable|string|max:1000',
                'capacity' => 'required|integer|min:1|max:200',
                'area' => 'nullable|numeric|min:0.1|max:1000',
                'features' => 'nullable|array',
                'hourly_rate' => 'nullable|numeric|min:0|max:10000',
                'daily_rate' => 'nullable|numeric|min:0|max:50000',
                'floor_level' => 'required|integer|min:0|max:50',
                'location_details' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $space = Space::create($validatedData);

            DB::commit();

            Log::info('New space created', [
                'space_id' => $space->id,
                'name' => $space->name,
                'number' => $space->number,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Espace créé avec succès!',
                'data' => [
                    'space' => [
                        'id' => $space->id,
                        'name' => $space->name,
                        'number' => $space->number,
                        'full_name' => $space->full_name,
                        'type_label' => $space->type_label,
                        'status_label' => $space->status_label,
                    ]
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create space', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'espace',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show space details
     */
    public function show(Space $space): JsonResponse
    {
        try {
            $space->load([
                'reservations' => function ($query) {
                    $query->with('user')->orderBy('starts_at', 'desc')->take(10);
                },
                'maintenanceRecords' => function ($query) {
                    $query->orderBy('scheduled_at', 'desc')->take(5);
                }
            ]);
            
            $data = [
                'id' => $space->id,
                'name' => $space->name,
                'number' => $space->number,
                'full_name' => $space->full_name,
                'type' => $space->type,
                'type_label' => $space->type_label,
                'description' => $space->description,
                'capacity' => $space->capacity,
                'area' => $space->area,
                'features' => $space->features,
                'status' => $space->status,
                'status_label' => $space->status_label,
                'floor_level' => $space->floor_level,
                'location_details' => $space->location_details,
                'hourly_rate' => $space->hourly_rate,
                'daily_rate' => $space->daily_rate,
                'current_occupancy' => $space->current_occupancy,
                'occupancy_rate' => $space->occupancy_rate,
                'is_available' => $space->is_available,
                'iot_status' => $space->iot_status,
                'next_available_slot' => $space->next_available_slot?->format('d/m/Y H:i'),
                'utilization_rate' => $space->getUtilizationRate(),
                'monthly_revenue' => $space->getRevenue(),
                'current_reservation' => $space->currentReservation() ? [
                    'id' => $space->currentReservation()->id,
                    'user_name' => $space->currentReservation()->user->display_name,
                    'starts_at' => $space->currentReservation()->starts_at->format('d/m/Y H:i'),
                    'ends_at' => $space->currentReservation()->ends_at->format('d/m/Y H:i'),
                    'expected_attendees' => $space->currentReservation()->expected_attendees,
                    'purpose' => $space->currentReservation()->purpose,
                ] : null,
                'upcoming_reservations' => $space->upcomingReservations()->get()->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'user_name' => $reservation->user->display_name,
                        'starts_at' => $reservation->starts_at->format('d/m/Y H:i'),
                        'ends_at' => $reservation->ends_at->format('d/m/Y H:i'),
                        'expected_attendees' => $reservation->expected_attendees,
                        'purpose' => $reservation->purpose,
                        'status_label' => $reservation->status_label,
                    ];
                }),
                'recent_reservations' => $space->reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'user_name' => $reservation->user->display_name,
                        'starts_at' => $reservation->starts_at->format('d/m/Y H:i'),
                        'ends_at' => $reservation->ends_at->format('d/m/Y H:i'),
                        'status_label' => $reservation->status_label,
                        'total_cost' => $reservation->total_cost,
                    ];
                }),
                'maintenance_records' => $space->maintenanceRecords->map(function ($maintenance) {
                    return [
                        'id' => $maintenance->id,
                        'title' => $maintenance->title,
                        'type_label' => $maintenance->type_label,
                        'priority_label' => $maintenance->priority_label,
                        'status_label' => $maintenance->status_label,
                        'scheduled_at' => $maintenance->scheduled_at->format('d/m/Y H:i'),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get space details', [
                'space_id' => $space->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de l\'espace'
            ], 500);
        }
    }

    /**
     * Update space
     */
    public function update(Request $request, Space $space): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'number' => ['required', 'string', 'max:50', Rule::unique('spaces')->ignore($space)],
                'type' => ['required', Rule::in(array_keys(Space::getAvailableTypes()))],
                'description' => 'nullable|string|max:1000',
                'capacity' => 'required|integer|min:1|max:200',
                'area' => 'nullable|numeric|min:0.1|max:1000',
                'features' => 'nullable|array',
                'hourly_rate' => 'nullable|numeric|min:0|max:10000',
                'daily_rate' => 'nullable|numeric|min:0|max:50000',
                'floor_level' => 'required|integer|min:0|max:50',
                'location_details' => 'nullable|string|max:500',
                'is_active' => 'boolean',
            ]);

            $space->update($validatedData);

            Log::info('Space updated', [
                'space_id' => $space->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Espace mis à jour avec succès',
                'data' => [
                    'space' => [
                        'id' => $space->id,
                        'name' => $space->name,
                        'number' => $space->number,
                        'full_name' => $space->full_name,
                        'type_label' => $space->type_label,
                        'status_label' => $space->status_label,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update space', [
                'space_id' => $space->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'espace',
                'errors' => $request->validator ? $request->validator->errors() : []
            ], 422);
        }
    }

    /**
     * Create a reservation for a space
     */
    public function createReservation(Request $request, Space $space): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'starts_at' => 'required|date|after:now',
                'ends_at' => 'required|date|after:starts_at',
                'expected_attendees' => 'required|integer|min:1|max:' . $space->capacity,
                'purpose' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
            ]);

            $startsAt = Carbon::parse($validatedData['starts_at']);
            $endsAt = Carbon::parse($validatedData['ends_at']);

            // Check if space has conflicting reservations
            if ($space->hasConflictingReservation($startsAt, $endsAt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'espace est déjà réservé pour cette période'
                ], 422);
            }

            $totalCost = $space->calculateCost($startsAt, $endsAt);

            $reservation = SpaceReservation::create([
                'space_id' => $space->id,
                'user_id' => $validatedData['user_id'],
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => SpaceReservation::STATUS_CONFIRMED,
                'expected_attendees' => $validatedData['expected_attendees'],
                'purpose' => $validatedData['purpose'],
                'notes' => $validatedData['notes'],
                'total_cost' => $totalCost,
            ]);

            $space->updateStatus();

            Log::info('Space reservation created', [
                'reservation_id' => $reservation->id,
                'space_id' => $space->id,
                'user_id' => $validatedData['user_id'],
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'data' => [
                    'reservation' => [
                        'id' => $reservation->id,
                        'starts_at' => $reservation->starts_at->format('d/m/Y H:i'),
                        'ends_at' => $reservation->ends_at->format('d/m/Y H:i'),
                        'total_cost' => $reservation->total_cost,
                    ]
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to create reservation', [
                'space_id' => $space->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la réservation',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Schedule maintenance for a space
     */
    public function scheduleMaintenance(Request $request, Space $space): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'type' => ['required', Rule::in(array_keys(SpaceMaintenance::getAvailableTypes()))],
                'priority' => ['required', Rule::in(array_keys(SpaceMaintenance::getAvailablePriorities()))],
                'scheduled_at' => 'required|date|after:now',
                'estimated_cost' => 'nullable|numeric|min:0|max:100000',
                'assigned_to' => 'nullable|string|max:255',
                'parts_needed' => 'nullable|array',
            ]);

            $maintenance = SpaceMaintenance::create([
                'space_id' => $space->id,
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'type' => $validatedData['type'],
                'priority' => $validatedData['priority'],
                'scheduled_at' => $validatedData['scheduled_at'],
                'estimated_cost' => $validatedData['estimated_cost'],
                'assigned_to' => $validatedData['assigned_to'],
                'parts_needed' => $validatedData['parts_needed'],
                'status' => SpaceMaintenance::STATUS_SCHEDULED,
                'created_by' => auth()->id(),
            ]);

            Log::info('Maintenance scheduled', [
                'maintenance_id' => $maintenance->id,
                'space_id' => $space->id,
                'scheduled_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance programmée avec succès',
                'data' => [
                    'maintenance' => [
                        'id' => $maintenance->id,
                        'title' => $maintenance->title,
                        'scheduled_at' => $maintenance->scheduled_at->format('d/m/Y H:i'),
                        'priority_label' => $maintenance->priority_label,
                    ]
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to schedule maintenance', [
                'space_id' => $space->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la programmation de la maintenance',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get space availability
     */
    public function getAvailability(Request $request, Space $space): JsonResponse
    {
        try {
            $date = $request->get('date', today()->format('Y-m-d'));
            $requestedDate = Carbon::parse($date);

            $reservations = $space->reservations()
                ->whereDate('starts_at', $requestedDate)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->orderBy('starts_at')
                ->get(['starts_at', 'ends_at', 'expected_attendees']);

            $availableSlots = $this->calculateAvailableSlots($requestedDate, $reservations);

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $requestedDate->format('d/m/Y'),
                    'reservations' => $reservations,
                    'available_slots' => $availableSlots,
                    'is_available_now' => $space->is_available,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get space availability', [
                'space_id' => $space->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la disponibilité'
            ], 500);
        }
    }

    /**
     * Calculate available time slots for a date
     */
    private function calculateAvailableSlots(Carbon $date, $reservations): array
    {
        $slots = [];
        $startHour = 8; // 8:00 AM
        $endHour = 20;  // 8:00 PM
        
        $currentTime = $date->copy()->hour($startHour)->minute(0)->second(0);
        $endTime = $date->copy()->hour($endHour)->minute(0)->second(0);

        while ($currentTime < $endTime) {
            $slotEnd = $currentTime->copy()->addHour();
            
            $isAvailable = true;
            foreach ($reservations as $reservation) {
                if ($currentTime < $reservation->ends_at && $slotEnd > $reservation->starts_at) {
                    $isAvailable = false;
                    break;
                }
            }

            $slots[] = [
                'start' => $currentTime->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'available' => $isAvailable,
            ];

            $currentTime->addHour();
        }

        return $slots;
    }

    /**
     * Get spaces statistics for AJAX
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->getSpacesStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get spaces stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Get dashboard data for spaces
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $stats = $this->getSpacesStatistics();
            
            // Get hourly occupancy data for today
            $hourlyOccupancy = [];
            for ($hour = 8; $hour <= 20; $hour++) {
                $timeSlot = today()->hour($hour);
                $occupancy = SpaceReservation::where('starts_at', '<=', $timeSlot)
                    ->where('ends_at', '>', $timeSlot)
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->count();
                
                $totalSpaces = Space::where('is_active', true)->count();
                $occupancyRate = $totalSpaces > 0 ? round(($occupancy / $totalSpaces) * 100, 1) : 0;
                
                $hourlyOccupancy[] = [
                    'hour' => $hour . 'h',
                    'occupancy' => $occupancyRate
                ];
            }

            // Get space type distribution
            $spaceTypes = Space::where('is_active', true)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'label' => (new Space(['type' => $item->type]))->type_label,
                        'count' => $item->count
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'hourly_occupancy' => $hourlyOccupancy,
                    'space_types' => $spaceTypes,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get dashboard data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données du tableau de bord'
            ], 500);
        }
    }
}