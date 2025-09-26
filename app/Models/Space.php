<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Space extends Model
{
    use HasFactory;

    // Constants for space types
    const TYPE_OFFICE = 'office';
    const TYPE_MEETING_ROOM = 'meeting_room';
    const TYPE_OPEN_SPACE = 'open_space';
    const TYPE_PHONE_BOOTH = 'phone_booth';
    const TYPE_OTHER = 'other';

    // Constants for space status
    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_RESERVED = 'reserved';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_OUT_OF_ORDER = 'out_of_order';

    protected $fillable = [
        'name',
        'number',
        'type',
        'description',
        'capacity',
        'area',
        'features',
        'status',
        'hourly_rate',
        'daily_rate',
        'iot_sensors',
        'is_active',
        'floor_level',
        'location_details',
    ];

    protected $casts = [
        'features' => 'array',
        'iot_sensors' => 'array',
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'area' => 'decimal:2',
    ];

    // Relationships
    public function reservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(SpaceMaintenance::class);
    }

    /**
     * Get the current reservation (for eager loading)
     */
    public function currentReservationRelation(): HasOne
    {
        return $this->hasOne(SpaceReservation::class)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->latest('starts_at');
    }

    /**
     * Get upcoming reservations (for eager loading)
     */
    public function upcomingReservationsRelation(): HasMany
    {
        return $this->hasMany(SpaceReservation::class)
            ->where('starts_at', '>', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at')
            ->limit(5);
    }

    /**
     * Get active maintenance (for eager loading)
     */
    public function activeMaintenanceRelation(): HasMany
    {
        return $this->hasMany(SpaceMaintenance::class)
            ->whereIn('status', ['scheduled', 'in_progress']);
    }

    /**
     * Get the current reservation (method version)
     */
    public function currentReservation(): ?SpaceReservation
    {
        return $this->reservations()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->first();
    }

    /**
     * Get upcoming reservations (method version)
     */
    public function upcomingReservations()
    {
        return $this->reservations()
            ->where('starts_at', '>', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at')
            ->take(5);
    }

    /**
     * Get active maintenance (method version)
     */
    public function activeMaintenance()
    {
        return $this->maintenanceRecords()
            ->whereIn('status', ['scheduled', 'in_progress']);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
                    ->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByFloor($query, $floor)
    {
        return $query->where('floor_level', $floor);
    }

    public function scopeWithCapacity($query, $minCapacity)
    {
        return $query->where('capacity', '>=', $minCapacity);
    }

    // Accessors & Mutators
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_OFFICE => 'Bureau privé',
            self::TYPE_MEETING_ROOM => 'Salle de réunion',
            self::TYPE_OPEN_SPACE => 'Espace ouvert',
            self::TYPE_PHONE_BOOTH => 'Cabine téléphonique',
            self::TYPE_OTHER => 'Autre',
            default => 'Inconnu'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'Disponible',
            self::STATUS_OCCUPIED => 'Occupé',
            self::STATUS_RESERVED => 'Réservé',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_OUT_OF_ORDER => 'Hors service',
            default => 'Inconnu'
        };
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->number})";
    }

    public function getCurrentOccupancyAttribute(): int
    {
        $currentReservation = $this->currentReservation();
        return $currentReservation ? $currentReservation->expected_attendees : 0;
    }

    public function getOccupancyRateAttribute(): float
    {
        if ($this->capacity === 0) {
            return 0;
        }

        return round(($this->current_occupancy / $this->capacity) * 100, 1);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && 
               $this->is_active && 
               !$this->hasConflictingReservation();
    }

    public function getIotStatusAttribute(): string
    {
        // Simulate IoT connectivity status
        if (!$this->iot_sensors || empty($this->iot_sensors)) {
            return 'offline';
        }

        // Check if any sensor is reporting (simulate random connectivity)
        $isOnline = rand(1, 100) > 15; // 85% chance of being online
        return $isOnline ? 'online' : 'offline';
    }

    public function getNextAvailableSlotAttribute(): ?Carbon
    {
        $nextReservation = $this->reservations()
            ->where('starts_at', '>', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at')
            ->first();

        return $nextReservation ? $nextReservation->starts_at : now();
    }

    // Methods
    public function hasConflictingReservation(Carbon $start = null, Carbon $end = null): bool
    {
        $start = $start ?? now();
        $end = $end ?? now()->addHour();

        return $this->reservations()
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('starts_at', '<=', $start)
                      ->where('ends_at', '>', $start);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->where('starts_at', '<', $end)
                      ->where('ends_at', '>=', $end);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->where('starts_at', '>=', $start)
                      ->where('ends_at', '<=', $end);
                });
            })
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->exists();
    }

    public function calculateCost(Carbon $start, Carbon $end): float
    {
        $hours = $start->diffInHours($end);
        
        if ($this->hourly_rate && $hours < 8) {
            return $hours * $this->hourly_rate;
        }

        if ($this->daily_rate) {
            $days = ceil($hours / 8);
            return $days * $this->daily_rate;
        }

        return 0;
    }

    public function updateStatus(): void
    {
        // Check if there's active maintenance
        if ($this->activeMaintenance()->exists()) {
            $this->update(['status' => self::STATUS_MAINTENANCE]);
            return;
        }

        // Check current reservation
        $currentReservation = $this->currentReservation();
        
        if ($currentReservation) {
            $status = $currentReservation->status === 'checked_in' 
                ? self::STATUS_OCCUPIED 
                : self::STATUS_RESERVED;
            
            $this->update(['status' => $status]);
            return;
        }

        // Check upcoming reservations (within next hour)
        $upcomingReservation = $this->reservations()
            ->where('starts_at', '<=', now()->addHour())
            ->where('starts_at', '>', now())
            ->where('status', 'confirmed')
            ->first();

        if ($upcomingReservation) {
            $this->update(['status' => self::STATUS_RESERVED]);
            return;
        }

        // Default to available
        $this->update(['status' => self::STATUS_AVAILABLE]);
    }

    public function getUtilizationRate(Carbon $startDate = null, Carbon $endDate = null): float
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $totalHours = $startDate->diffInHours($endDate);
        
        $bookedHours = $this->reservations()
            ->where('starts_at', '>=', $startDate)
            ->where('ends_at', '<=', $endDate)
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->get()
            ->sum(function ($reservation) {
                return $reservation->starts_at->diffInHours($reservation->ends_at);
            });

        return $totalHours > 0 ? round(($bookedHours / $totalHours) * 100, 1) : 0;
    }

    // Static methods
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_OFFICE => 'Bureau privé',
            self::TYPE_MEETING_ROOM => 'Salle de réunion',
            self::TYPE_OPEN_SPACE => 'Espace ouvert',
            self::TYPE_PHONE_BOOTH => 'Cabine téléphonique',
            self::TYPE_OTHER => 'Autre',
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Disponible',
            self::STATUS_OCCUPIED => 'Occupé',
            self::STATUS_RESERVED => 'Réservé',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_OUT_OF_ORDER => 'Hors service',
        ];
    }

    public static function getCommonFeatures(): array
    {
        return [
            'wifi' => 'WiFi',
            'projector' => 'Projecteur',
            'tv_screen' => 'Écran TV',
            'whiteboard' => 'Tableau blanc',
            'flipchart' => 'Paper board',
            'air_conditioning' => 'Climatisation',
            'natural_light' => 'Lumière naturelle',
            'power_outlets' => 'Prises électriques',
            'phone_line' => 'Ligne téléphonique',
            'ergonomic_chairs' => 'Chaises ergonomiques',
            'standing_desk' => 'Bureau debout',
            'coffee_machine' => 'Machine à café',
            'printer_access' => 'Accès imprimante',
            'soundproofing' => 'Insonorisation',
            'video_conference' => 'Visioconférence',
        ];
    }

    public function getTodaysReservations()
    {
        return $this->reservations()
            ->whereDate('starts_at', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at');
    }

    public function getRevenue(Carbon $startDate = null, Carbon $endDate = null): float
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        return $this->reservations()
            ->where('starts_at', '>=', $startDate)
            ->where('ends_at', '<=', $endDate)
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->sum('total_cost') ?? 0;
    }
}