<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Poll extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';

    const VISIBILITY_ALL = 'all';
    const VISIBILITY_ACTIVE = 'active';
    const VISIBILITY_PLAN = 'plan';
    const VISIBILITY_CUSTOM = 'custom';

    protected $fillable = [
        'title',
        'description',
        'options',
        'status',
        'visibility',
        'duration_days',
        'starts_at',
        'ends_at',
        'created_by',
        'allow_multiple_choices',
        'anonymous_voting',
        'total_votes',
    ];

    protected $casts = [
        'options' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'allow_multiple_choices' => 'boolean',
        'anonymous_voting' => 'boolean',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeEnded($query)
    {
        return $query->where('status', self::STATUS_ENDED);
    }

    public function scopePublished($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_ENDED]);
    }

    // Accessors & Mutators
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_ENDED => 'Terminé',
            default => 'Inconnu'
        };
    }

    public function getVisibilityLabelAttribute(): string
    {
        return match($this->visibility) {
            self::VISIBILITY_ALL => 'Tous les membres',
            self::VISIBILITY_ACTIVE => 'Membres actifs uniquement',
            self::VISIBILITY_PLAN => 'Par plan d\'abonnement',
            self::VISIBILITY_CUSTOM => 'Sélection personnalisée',
            default => 'Inconnu'
        };
    }

    public function getParticipationRateAttribute(): float
    {
        $totalMembers = User::where('role', User::ROLE_USER)->where('is_active', true)->count();
        
        if ($totalMembers === 0) {
            return 0;
        }

        return round(($this->votes->count() / $totalMembers) * 100, 1);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->status !== self::STATUS_ACTIVE || !$this->ends_at) {
            return null;
        }

        $now = Carbon::now();
        
        if ($this->ends_at->isPast()) {
            return 0;
        }

        return $now->diffInDays($this->ends_at, false);
    }

    public function getTimeRemainingAttribute(): string
    {
        if ($this->status !== self::STATUS_ACTIVE || !$this->ends_at) {
            return '';
        }

        $now = Carbon::now();
        
        if ($this->ends_at->isPast()) {
            return 'Expiré';
        }

        return $this->ends_at->diffForHumans();
    }

    public function getVoteResultsAttribute(): array
    {
        $results = [];
        $totalVotes = $this->votes->count();

        foreach ($this->options as $index => $option) {
            $votes = $this->votes->filter(function ($vote) use ($index) {
                return in_array($index, $vote->selected_options);
            })->count();

            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0;

            $results[] = [
                'option' => $option,
                'votes' => $votes,
                'percentage' => $percentage
            ];
        }

        return $results;
    }

    // Methods
    public function start(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays($this->duration_days)
        ]);

        return true;
    }

    public function end(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $this->update(['status' => self::STATUS_ENDED]);
        return true;
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function canUserVote(User $user): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        // Check if user already voted
        if ($this->votes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Check visibility restrictions
        switch ($this->visibility) {
            case self::VISIBILITY_ACTIVE:
                return $user->is_active;
            case self::VISIBILITY_PLAN:
                // Implementation depends on specific plan restrictions
                return true;
            case self::VISIBILITY_CUSTOM:
                // Implementation depends on custom restrictions
                return true;
            default:
                return true;
        }
    }

    public function vote(User $user, array $selectedOptions): bool
    {
        if (!$this->canUserVote($user)) {
            return false;
        }

        // Validate selected options
        foreach ($selectedOptions as $optionIndex) {
            if (!isset($this->options[$optionIndex])) {
                return false;
            }
        }

        // Check multiple choices restriction
        if (!$this->allow_multiple_choices && count($selectedOptions) > 1) {
            return false;
        }

        // Create vote
        $this->votes()->create([
            'user_id' => $user->id,
            'selected_options' => $selectedOptions
        ]);

        // Update total votes count
        $this->increment('total_votes');

        return true;
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_ENDED => 'Terminé',
        ];
    }

    public static function getAvailableVisibilities(): array
    {
        return [
            self::VISIBILITY_ALL => 'Tous les membres',
            self::VISIBILITY_ACTIVE => 'Membres actifs uniquement',
            self::VISIBILITY_PLAN => 'Par plan d\'abonnement',
            self::VISIBILITY_CUSTOM => 'Sélection personnalisée',
        ];
    }

    // Check if polls that are active should be ended
    public static function checkExpiredPolls(): int
    {
        $expiredPolls = self::active()
            ->where('ends_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredPolls as $poll) {
            if ($poll->end()) {
                $count++;
            }
        }

        return $count;
    }
}