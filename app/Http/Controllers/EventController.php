<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display events management page
     */
    public function index(): View
    {
        $events = Event::with(['creator', 'participants'])
            ->orderBy('starts_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_events' => Event::count(),
            'total_participants' => Event::getTotalParticipants(),
            'upcoming_events' => Event::getUpcomingCount(),
            'average_rating' => Event::getAverageRating(),
        ];

        $upcoming_events = Event::published()
            ->upcoming()
            ->orderBy('starts_at')
            ->take(5)
            ->get();

        return view('admin.evenements', compact('events', 'stats', 'upcoming_events'));
    }

    /**
     * Get events data for API
     */
    public function getEvents(Request $request): JsonResponse
    {
        try {
            $query = Event::with(['creator', 'participants']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('starts_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('starts_at', '<=', $request->date_to);
            }

            $events = $query->orderBy('starts_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $events->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'type' => $event->type,
                        'type_label' => $event->type_label,
                        'starts_at' => $event->starts_at,
                        'ends_at' => $event->ends_at,
                        'formatted_date' => $event->formatted_date,
                        'formatted_time' => $event->formatted_time,
                        'duration' => $event->duration,
                        'capacity' => $event->capacity,
                        'location' => $event->location,
                        'location_label' => $event->location_label,
                        'price' => $event->price,
                        'price_format' => $event->price_format,
                        'status' => $event->status,
                        'status_label' => $event->status_label,
                        'participants_count' => $event->participants_count,
                        'available_spots' => $event->available_spots,
                        'is_full' => $event->is_full,
                        'occupancy_rate' => $event->occupancy_rate,
                        'is_upcoming' => $event->is_upcoming,
                        'creator' => $event->creator->display_name,
                        'participants' => $event->participants->take(5)->map(function ($participant) {
                            return [
                                'id' => $participant->id,
                                'name' => $participant->display_name,
                                'initials' => strtoupper(substr($participant->display_name, 0, 2)),
                            ];
                        }),
                    ];
                })
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get events', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements'
            ], 500);
        }
    }

    /**
     * Store a new event
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:networking,workshop,conference,social,training',
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i',
                'duration' => 'required|integer|min:15|max:480', // 15 min to 8 hours
                'capacity' => 'required|integer|min:1|max:500',
                'location' => 'required|string',
                'price' => 'nullable|numeric|min:0',
                'status' => 'nullable|in:draft,published',
            ]);
            
            // Ensure duration is an integer
            $validated['duration'] = (int) $validated['duration'];
            $validated['capacity'] = (int) $validated['capacity'];
            $validated['price'] = $validated['price'] ? (float) $validated['price'] : 0;

            DB::beginTransaction();

            $starts_at = Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['time']);
            $ends_at = $starts_at->copy()->addMinutes((int) $validated['duration']);

            $event = Event::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'starts_at' => $starts_at,
                'ends_at' => $ends_at,
                'duration' => $validated['duration'],
                'capacity' => $validated['capacity'],
                'location' => $validated['location'],
                'price' => $validated['price'] ?? 0,
                'status' => $validated['status'] ?? Event::STATUS_PUBLISHED,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            Log::info('Event created', [
                'event_id' => $event->id,
                'title' => $event->title,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Événement créé avec succès !',
                'data' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'type' => $event->type,
                    'formatted_date' => $event->formatted_date,
                    'formatted_time' => $event->formatted_time,
                    'location_label' => $event->location_label,
                    'price_format' => $event->price_format,
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Event creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'événement',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update an existing event
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:networking,workshop,conference,social,training',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'duration' => 'required|integer|min:15|max:480',
                'capacity' => 'required|integer|min:1|max:500',
                'location' => 'required|string',
                'price' => 'nullable|numeric|min:0',
                'status' => 'nullable|in:draft,published,cancelled',
            ]);
            
            // Ensure proper types
            $validated['duration'] = (int) $validated['duration'];
            $validated['capacity'] = (int) $validated['capacity'];
            $validated['price'] = $validated['price'] ? (float) $validated['price'] : 0;

            $starts_at = Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['time']);
            $ends_at = $starts_at->copy()->addMinutes((int) $validated['duration']);

            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'starts_at' => $starts_at,
                'ends_at' => $ends_at,
                'duration' => $validated['duration'],
                'capacity' => $validated['capacity'],
                'location' => $validated['location'],
                'price' => $validated['price'] ?? 0,
                'status' => $validated['status'] ?? $event->status,
            ]);

            Log::info('Event updated', [
                'event_id' => $event->id,
                'title' => $event->title,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Événement mis à jour avec succès !',
            ]);

        } catch (Exception $e) {
            Log::error('Event update failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'événement',
            ], 422);
        }
    }

    /**
     * Delete an event
     */
    public function destroy(Event $event): JsonResponse
    {
        try {
            $event->delete();

            Log::info('Event deleted', [
                'event_id' => $event->id,
                'deleted_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Événement supprimé avec succès !',
            ]);

        } catch (Exception $e) {
            Log::error('Event deletion failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'événement',
            ], 500);
        }
    }

    /**
     * User participates in event
     */
    public function participate(Request $request, Event $event): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($event->isUserParticipating($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous participez déjà à cet événement'
                ], 400);
            }

            if (!$event->canUserParticipate($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas participer à cet événement'
                ], 400);
            }

            $success = $event->addParticipant($user);
            $status = $event->is_full ? 'waitlist' : 'registered';

            if ($success) {
                $message = $status === 'waitlist' 
                    ? 'Vous avez été ajouté à la liste d\'attente'
                    : 'Inscription réussie !';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'status' => $status,
                        'participants_count' => $event->fresh()->participants_count,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription'
            ], 400);

        } catch (Exception $e) {
            Log::error('Event participation failed', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription'
            ], 500);
        }
    }

    /**
     * User cancels participation in event
     */
    public function cancelParticipation(Request $request, Event $event): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$event->isUserParticipating($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne participez pas à cet événement'
                ], 400);
            }

            $success = $event->removeParticipant($user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Participation annulée avec succès',
                    'data' => [
                        'participants_count' => $event->fresh()->participants_count,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation'
            ], 400);

        } catch (Exception $e) {
            Log::error('Event participation cancellation failed', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation'
            ], 500);
        }
    }

    /**
     * Get event statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_events' => Event::count(),
                'total_participants' => Event::getTotalParticipants(),
                'upcoming_events' => Event::getUpcomingCount(),
                'average_rating' => Event::getAverageRating(),
                'events_this_month' => Event::getEventsThisMonth(),
                'participation_by_type' => Event::select('type')
                    ->withCount('participants')
                    ->get()
                    ->groupBy('type')
                    ->map(function ($group) {
                        return $group->sum('participants_count');
                    }),
                'events_by_month' => Event::selectRaw('MONTH(starts_at) as month, COUNT(*) as count')
                    ->whereYear('starts_at', now()->year)
                    ->groupBy('month')
                    ->pluck('count', 'month'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get event stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Get calendar events
     */
    public function getCalendarEvents(Request $request): JsonResponse
    {
        try {
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);

            $events = Event::whereMonth('starts_at', $month)
                ->whereYear('starts_at', $year)
                ->published()
                ->get()
                ->groupBy(function ($event) {
                    return $event->starts_at->day;
                })
                ->map(function ($group) {
                    return $group->count();
                });

            return response()->json([
                'success' => true,
                'data' => $events
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get calendar events', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du calendrier'
            ], 500);
        }
    }
}