<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Define role enum constants
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    
    // Define account type constants
    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_COMPANY = 'company';

    protected $fillable = [
        // Account info
        'account_type',
        'role',
        
        // Individual fields
        'prenom',
        'nom',
        'cin',
        
        // Company fields
        'company_name',
        'rc',
        'ice',
        'legal_representative',
        
        // Contact info
        'email',
        'phone',
        'address',
        
        // Membership
        'membership_plan',
        'price',
        'billing_cycle',
        
        // Auth & preferences
        'password',
        'newsletter',
        'terms_accepted',
        
        // Status
        'is_active',
        'last_login_at',

        //user profession and avatar
        'avatar',
        'profession',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => self::ROLE_USER,
        'is_active' => true,
        'terms_accepted' => true,
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'newsletter' => 'boolean',
        'terms_accepted' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // Accessors
    public function getFullNameAttribute(): string
    {
        if ($this->account_type === 'individual') {
            return trim($this->prenom . ' ' . $this->nom);
        }
        return $this->company_name ?? 'Unknown';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->account_type === 'individual' 
            ? $this->full_name 
            : $this->company_name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByAccountType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeByMembershipPlan($query, $plan)
    {
        return $query->where('membership_plan', $plan);
    }

    // Methods
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isIndividual(): bool
    {
        return $this->account_type === self::TYPE_INDIVIDUAL;
    }

    public function isCompany(): bool
    {
        return $this->account_type === self::TYPE_COMPANY;
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    // Static methods for getting available options
    public static function getRoles(): array
    {
        return [
            self::ROLE_USER => 'Utilisateur',
            self::ROLE_ADMIN => 'Administrateur',
        ];
    }

    public static function getAccountTypes(): array
    {
        return [
            self::TYPE_INDIVIDUAL => 'Particulier',
            self::TYPE_COMPANY => 'Entreprise',
        ];
    }

    public static function getMembershipPlans(): array
    {
        return [
            'hot-desk' => 'Hot Desk',
            'bureau-dedie' => 'Bureau Dédié',
            'bureau-prive' => 'Bureau Privé',
        ];
    }

    public static function getBillingCycles(): array
    {
        return [
            'daily' => 'Journalier',
            'weekly' => 'Semaine',
            'biweekly' => 'Deux semaines',
            'monthly' => 'Mensuel',
        ];
    }

    /**
     * Get billing cycles available for a specific membership plan
     */
    public static function getBillingCyclesForPlan(string $plan): array
    {
        return match($plan) {
            'hot-desk' => ['daily' => 'Journalier'], // Hot desk is usually daily only
            'bureau-dedie' => [
                'daily' => 'Journalier',
                'weekly' => 'Semaine', 
                'biweekly' => 'Deux semaines',
                'monthly' => 'Mensuel',
            ],
            'bureau-prive' => [
                'daily' => 'Journalier',
                'weekly' => 'Semaine',
                'biweekly' => 'Deux semaines', 
                'monthly' => 'Mensuel',
            ],
            default => self::getBillingCycles(),
        };
    }

    /**
     * Calculate total price based on billing cycle
     */
    public function calculateTotalPrice(): float
    {
        $basePrice = $this->price;
        
        return match($this->billing_cycle) {
            'daily' => $basePrice,
            'weekly' => $basePrice * 7,
            'biweekly' => $basePrice * 14,
            'monthly' => $basePrice * 30,
            default => $basePrice,
        };
    }

    /**
     * Get price description with billing cycle
     */
    public function getPriceDescriptionAttribute(): string
    {
        $price = number_format($this->price, 2);
        $cycle = $this->billing_cycle_label;
        
        return "{$price} MAD / {$cycle}";
    }

    public function getMembershipPlanLabelAttribute(): string
    {
        return match($this->membership_plan) {
            'hot-desk' => 'Hot Desk',
            'bureau-dedie' => 'Bureau Dédié',
            'bureau-prive' => 'Bureau Privé',
            default => 'Unknown Plan'
        };
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'daily' => 'Journalier',
            'weekly' => 'Semaine',
            'biweekly' => 'Deux semaines',
            'monthly' => 'Mensuel',
            default => 'Unknown Cycle'
        };
    }
// Relationships for polls
public function createdPolls(): HasMany
{
    return $this->hasMany(Poll::class, 'created_by');
}

public function pollVotes(): HasMany
{
    return $this->hasMany(PollVote::class);
}

// Methods for poll functionality
public function hasVotedOnPoll(Poll $poll): bool
{
    return $this->pollVotes()->where('poll_id', $poll->id)->exists();
}

public function getVoteForPoll(Poll $poll): ?PollVote
{
    return $this->pollVotes()->where('poll_id', $poll->id)->first();
}

public function canCreatePolls(): bool
{
    return $this->isAdmin();
}

public function canVoteOnPolls(): bool
{
    return $this->is_active && $this->role === self::ROLE_USER;
}

// Space-related relationships
public function spaceReservations(): HasMany
{
    return $this->hasMany(SpaceReservation::class);
}

public function checkedInReservations(): HasMany
{
    return $this->hasMany(SpaceReservation::class, 'checked_in_by');
}

public function createdMaintenanceRecords(): HasMany
{
    return $this->hasMany(SpaceMaintenance::class, 'created_by');
}

// Methods for space functionality
public function hasActiveReservation(): bool
{
    return $this->spaceReservations()
                ->currentlyActive()
                ->exists();
}

public function getCurrentReservation(): ?SpaceReservation
{
    return $this->spaceReservations()
                ->currentlyActive()
                ->first();
}

public function getUpcomingReservations()
{
    return $this->spaceReservations()
                ->upcoming()
                ->where('status', '!=', 'cancelled')
                ->orderBy('starts_at')
                ->take(5);
}

public function canReserveSpace(Space $space): bool
{
    return $this->is_active && $this->role === self::ROLE_USER;
}

public function getTotalSpaceUsageHours(): float
{
    return $this->spaceReservations()
                ->whereIn('status', ['completed', 'checked_in'])
                ->get()
                ->sum(function ($reservation) {
                    return $reservation->duration_hours;
                });
}

public function getSpaceReservationsRevenue(): float
{
    return $this->spaceReservations()
                ->whereIn('status', ['completed', 'checked_in', 'confirmed'])
                ->sum('total_cost') ?? 0;
}



// Event-related relationships
public function createdEvents(): HasMany
{
    return $this->hasMany(Event::class, 'created_by');
}

public function events(): BelongsToMany
{
    return $this->belongsToMany(Event::class)
        ->withPivot('status', 'registered_at', 'notes')
        ->withTimestamps();
}

public function registeredEvents(): BelongsToMany
{
    return $this->events()->wherePivot('status', 'registered');
}

public function waitlistEvents(): BelongsToMany
{
    return $this->events()->wherePivot('status', 'waitlist');
}

// Methods for event functionality
public function isParticipatingInEvent(Event $event): bool
{
    return $this->events()->where('event_id', $event->id)->exists();
}

public function getEventParticipationStatus(Event $event): ?string
{
    $pivot = $this->events()->where('event_id', $event->id)->first()?->pivot;
    return $pivot?->status;
}

public function canCreateEvents(): bool
{
    return $this->isAdmin();
}

public function getAvatarUrlAttribute(): string
{
    if ($this->avatar && file_exists(public_path($this->avatar))) {
        return asset($this->avatar);
    }
    return '';
}

/**
 * Get initials for avatar
 */
public function getInitialsAttribute(): string
{
    if ($this->isIndividual()) {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }
    return strtoupper(substr($this->company_name, 0, 2));
}

}