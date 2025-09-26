<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    // Constants for event types
    const TYPE_NETWORKING = 'networking';
    const TYPE_WORKSHOP = 'workshop';
    const TYPE_CONFERENCE = 'conference';
    const TYPE_SOCIAL = 'social';
    const TYPE_TRAINING = 'training';

    // Constants for event status
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    // Constants for participation status
    const PARTICIPATION_REGISTERED = 'registered';
    const PARTICIPATION_WAITLIST = 'waitlist';
    const PARTICIPATION_ATTENDED = 'attended';
    const PARTICIPATION_CANCELLED = 'cancelled';

    protected $fillable = [
        'title',
        'description',
        'type',
        'starts_at',
        'ends_at',
        'duration',
        'capacity',
        'price',
        'location',
        'status',
        'notes',
        'additional_services',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price' => 'decimal:2',
        'additional_services' => 'array',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'registered_at', 'notes')
            ->withTimestamps();
    }

    public function registeredParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', self::PARTICIPATION_REGISTERED);
    }

    public function waitlistParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', self::PARTICIPATION_WAITLIST);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('starts_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors & Mutators
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_NETWORKING => 'Networking',
            self::TYPE_WORKSHOP => 'Workshop',
            self::TYPE_CONFERENCE => 'Conférence',
            self::TYPE_SOCIAL => 'Social',
            self::TYPE_TRAINING => 'Formation',
            default => 'Inconnu'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_PUBLISHED => 'Publié',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_COMPLETED => 'Terminé',
            default => 'Inconnu'
        };
    }

    public function getLocationLabelAttribute(): string
    {
        return match($this->location) {
            'main' => 'Salle principale',
            'meeting-a' => 'Salle de réunion A',
            'meeting-b' => 'Salle de réunion B',
            'open-space' => 'Espace ouvert',
            'terrace' => 'Terrasse',
            'external' => 'Lieu externe',
            default => $this->location
        };
    }

    public function getPriceFormatAttribute(): string
    {
        return $this->price == 0 ? 'Gratuit' : number_format($this->price, 2) . ' MAD';
    }

    public function getParticipantsCountAttribute(): int
    {
        return $this->registeredParticipants()->count();
    }

    public function getWaitlistCountAttribute(): int
    {
        return $this->waitlistParticipants()->count();
    }

    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->capacity - $this->participants_count);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->participants_count >= $this->capacity;
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->starts_at > now();
    }

    public function getIsOngoingAttribute(): bool
    {
        return $this->starts_at <= now() && $this->ends_at >= now();
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->ends_at < now();
    }

    public function getOccupancyRateAttribute(): float
    {
        if ($this->capacity === 0) return 0;
        return round(($this->participants_count / $this->capacity) * 100, 1);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->starts_at->format('d/m/Y');
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->starts_at->format('H:i') . ' - ' . $this->ends_at->format('H:i');
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration / 60, 2);
    }

    // Methods
    public function canUserParticipate(User $user): bool
    {
        return $this->status === self::STATUS_PUBLISHED &&
               $this->is_upcoming &&
               !$this->isUserParticipating($user);
    }

    public function isUserParticipating(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function getUserParticipationStatus(User $user): ?string
    {
        $pivot = $this->participants()->where('user_id', $user->id)->first()?->pivot;
        return $pivot?->status;
    }

    public function addParticipant(User $user): bool
    {
        if (!$this->canUserParticipate($user)) {
            return false;
        }

        $status = $this->is_full ? self::PARTICIPATION_WAITLIST : self::PARTICIPATION_REGISTERED;
        
        $this->participants()->attach($user->id, [
            'status' => $status,
            'registered_at' => now(),
        ]);

        return true;
    }

    public function removeParticipant(User $user): bool
    {
        if (!$this->isUserParticipating($user)) {
            return false;
        }

        $this->participants()->detach($user->id);

        // If there's a waitlist, promote first person
        if ($this->waitlistParticipants()->exists()) {
            $waitlistUser = $this->waitlistParticipants()->oldest('event_user.created_at')->first();
            if ($waitlistUser) {
                $this->participants()->updateExistingPivot($waitlistUser->id, [
                    'status' => self::PARTICIPATION_REGISTERED
                ]);
            }
        }

        return true;
    }

    public function markAsCompleted(): bool
    {
        if (!$this->is_completed) {
            return false;
        }

        $this->update(['status' => self::STATUS_COMPLETED]);
        return true;
    }

    public function cancel(string $reason = null): bool
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $this->notes . ($reason ? "\nAnnulation: {$reason}" : ''),
        ]);

        return true;
    }

    // Static methods
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_NETWORKING => 'Networking',
            self::TYPE_WORKSHOP => 'Workshop',
            self::TYPE_CONFERENCE => 'Conférence',
            self::TYPE_SOCIAL => 'Social',
            self::TYPE_TRAINING => 'Formation',
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_PUBLISHED => 'Publié',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_COMPLETED => 'Terminé',
        ];
    }

    public static function getAvailableLocations(): array
    {
        return [
            'main' => 'Salle principale',
            'meeting-a' => 'Salle de réunion A',
            'meeting-b' => 'Salle de réunion B',
            'open-space' => 'Espace ouvert',
            'terrace' => 'Terrasse',
            'external' => 'Lieu externe',
        ];
    }

    public static function getUpcomingCount(): int
    {
        return self::published()->upcoming()->count();
    }

    public static function getTotalParticipants(): int
    {
        return self::published()
            ->withCount(['registeredParticipants'])
            ->get()
            ->sum('registered_participants_count');
    }

    public static function getEventsThisMonth(): int
    {
        return self::published()->thisMonth()->count();
    }

    public static function getAverageRating(): float
    {
        // This would require a ratings system - for now return a static value
        return 4.8;
    }
}