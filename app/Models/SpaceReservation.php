<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SpaceReservation extends Model
{
    use HasFactory;

    // Constants for reservation status
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'space_id',
        'user_id',
        'starts_at',
        'ends_at',
        'status',
        'total_cost',
        'purpose',
        'expected_attendees',
        'additional_services',
        'notes',
        'checked_in_at',
        'checked_out_at',
        'checked_in_by',
        'is_recurring',
        'recurrence_rules',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'total_cost' => 'decimal:2',
        'additional_services' => 'array',
        'recurrence_rules' => 'array',
        'is_recurring' => 'boolean',
    ];

    // Relationships
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('starts_at', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeCurrentlyActive($query)
    {
        return $query->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySpace($query, $spaceId)
    {
        return $query->where('space_id', $spaceId);
    }

    // Accessors & Mutators
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_CHECKED_IN => 'En cours',
            self::STATUS_COMPLETED => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
            default => 'Inconnu'
        };
    }

    public function getDurationAttribute(): int
    {
        return $this->starts_at->diffInMinutes($this->ends_at);
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration / 60, 2);
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]) &&
               $this->starts_at <= now() &&
               $this->ends_at >= now();
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->starts_at > now() && 
               in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PENDING]);
    }

    public function getCanCheckInAttribute(): bool
    {
        return $this->status === self::STATUS_CONFIRMED &&
               $this->starts_at <= now()->addMinutes(15) && // Allow 15 min early check-in
               $this->starts_at >= now()->subMinutes(30); // And 30 min late check-in
    }

    public function getCanCheckOutAttribute(): bool
    {
        return $this->status === self::STATUS_CHECKED_IN;
    }

    public function getTimeUntilStartAttribute(): string
    {
        if ($this->starts_at <= now()) {
            return 'Commencé';
        }

        return $this->starts_at->diffForHumans();
    }

    public function getTimeRemainingAttribute(): string
    {
        if ($this->ends_at <= now()) {
            return 'Terminé';
        }

        return $this->ends_at->diffForHumans(null, true);
    }

    // Methods
    public function checkIn(User $checkedInBy = null): bool
    {
        if (!$this->can_check_in) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CHECKED_IN,
            'checked_in_at' => now(),
            'checked_in_by' => $checkedInBy ? $checkedInBy->id : $this->user_id,
        ]);

        // Update space status
        $this->space->updateStatus();

        return true;
    }

    public function checkOut(): bool
    {
        if (!$this->can_check_out) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'checked_out_at' => now(),
        ]);

        // Update space status
        $this->space->updateStatus();

        return true;
    }

    public function cancel(string $reason = null): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $this->notes . ($reason ? "\nAnnulation: {$reason}" : ''),
        ]);

        // Update space status
        $this->space->updateStatus();

        return true;
    }

    public function confirm(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update(['status' => self::STATUS_CONFIRMED]);

        // Update space status
        $this->space->updateStatus();

        return true;
    }

    public function hasConflict(): bool
    {
        return SpaceReservation::where('space_id', $this->space_id)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('starts_at', '<=', $this->starts_at)
                      ->where('ends_at', '>', $this->starts_at);
                })->orWhere(function ($q) {
                    $q->where('starts_at', '<', $this->ends_at)
                      ->where('ends_at', '>=', $this->ends_at);
                })->orWhere(function ($q) {
                    $q->where('starts_at', '>=', $this->starts_at)
                      ->where('ends_at', '<=', $this->ends_at);
                });
            })
            ->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN])
            ->exists();
    }

    public function calculateCost(): float
    {
        return $this->space->calculateCost($this->starts_at, $this->ends_at);
    }

    public function autoComplete(): bool
    {
        if ($this->status === self::STATUS_CHECKED_IN && $this->ends_at <= now()) {
            $this->update(['status' => self::STATUS_COMPLETED]);
            $this->space->updateStatus();
            return true;
        }

        return false;
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_CHECKED_IN => 'En cours',
            self::STATUS_COMPLETED => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
        ];
    }

    // Auto-complete expired check-ins
    public static function autoCompleteExpired(): int
    {
        $expiredReservations = self::where('status', self::STATUS_CHECKED_IN)
            ->where('ends_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredReservations as $reservation) {
            if ($reservation->autoComplete()) {
                $count++;
            }
        }

        return $count;
    }

    // Create recurring reservations
    public function createRecurringReservations(): array
    {
        if (!$this->is_recurring || !$this->recurrence_rules) {
            return [];
        }

        $createdReservations = [];
        $rules = $this->recurrence_rules;
        
        // Simple weekly recurrence example
        if ($rules['type'] === 'weekly' && isset($rules['weeks'])) {
            for ($i = 1; $i <= $rules['weeks']; $i++) {
                $newStartsAt = $this->starts_at->copy()->addWeeks($i);
                $newEndsAt = $this->ends_at->copy()->addWeeks($i);

                $newReservation = self::create([
                    'space_id' => $this->space_id,
                    'user_id' => $this->user_id,
                    'starts_at' => $newStartsAt,
                    'ends_at' => $newEndsAt,
                    'status' => self::STATUS_CONFIRMED,
                    'total_cost' => $this->total_cost,
                    'purpose' => $this->purpose,
                    'expected_attendees' => $this->expected_attendees,
                    'additional_services' => $this->additional_services,
                    'notes' => $this->notes . ' (Récurrence automatique)',
                    'is_recurring' => false, // Prevent infinite recursion
                ]);

                $createdReservations[] = $newReservation;
            }
        }

        return $createdReservations;
    }
}