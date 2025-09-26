<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceMaintenance extends Model
{
    use HasFactory;

    protected $table = 'space_maintenance';

    // Constants for maintenance types
    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_INSPECTION = 'inspection';

    // Constants for priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Constants for status
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_POSTPONED = 'postponed';

    protected $fillable = [
        'space_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'estimated_cost',
        'actual_cost',
        'assigned_to',
        'notes',
        'checklist',
        'parts_needed',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'checklist' => 'array',
        'parts_needed' => 'array',
    ];

    // Relationships
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_at', '<', now())
                    ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_POSTPONED]);
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('scheduled_at', [now(), now()->addDays($days)])
                    ->where('status', self::STATUS_SCHEDULED);
    }

    // Accessors & Mutators
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PREVENTIVE => 'Préventive',
            self::TYPE_CORRECTIVE => 'Corrective',
            self::TYPE_EMERGENCY => 'Urgence',
            self::TYPE_INSPECTION => 'Inspection',
            default => 'Inconnu'
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_MEDIUM => 'Moyenne',
            self::PRIORITY_HIGH => 'Haute',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Inconnu'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'Programmée',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_COMPLETED => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_POSTPONED => 'Reportée',
            default => 'Inconnu'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'success',
            self::PRIORITY_MEDIUM => 'warning',
            self::PRIORITY_HIGH => 'danger',
            self::PRIORITY_URGENT => 'danger',
            default => 'secondary'
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->scheduled_at < now() && 
               in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_POSTPONED]);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }

        return null;
    }

    public function getDurationHoursAttribute(): ?float
    {
        $duration = $this->duration;
        return $duration ? round($duration / 60, 2) : null;
    }

    public function getCostVarianceAttribute(): ?float
    {
        if ($this->estimated_cost && $this->actual_cost) {
            return $this->actual_cost - $this->estimated_cost;
        }

        return null;
    }

    public function getChecklistProgressAttribute(): array
    {
        if (!$this->checklist) {
            return ['completed' => 0, 'total' => 0, 'percentage' => 0];
        }

        $total = count($this->checklist);
        $completed = collect($this->checklist)->where('completed', true)->count();
        $percentage = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage
        ];
    }

    // Methods
    public function start(): bool
    {
        if ($this->status !== self::STATUS_SCHEDULED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        // Update space status to maintenance
        $this->space->update(['status' => Space::STATUS_MAINTENANCE]);

        return true;
    }

    public function complete(float $actualCost = null, string $notes = null): bool
    {
        if (!in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS])) {
            return false;
        }

        $updateData = [
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ];

        if ($actualCost !== null) {
            $updateData['actual_cost'] = $actualCost;
        }

        if ($notes !== null) {
            $updateData['notes'] = $this->notes . "\n" . $notes;
        }

        $this->update($updateData);

        // Update space status back to available
        $this->space->updateStatus();

        return true;
    }

    public function postpone(string $newDateTime, string $reason = null): bool
    {
        if (!in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_POSTPONED])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_POSTPONED,
            'scheduled_at' => $newDateTime,
            'notes' => $this->notes . ($reason ? "\nReporté: {$reason}" : ''),
        ]);

        return true;
    }

    public function cancel(string $reason = null): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $this->notes . ($reason ? "\nAnnulé: {$reason}" : ''),
        ]);

        // Update space status
        $this->space->updateStatus();

        return true;
    }

    public function updateChecklist(array $checklist): bool
    {
        $this->update(['checklist' => $checklist]);
        return true;
    }

    // Static methods
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_PREVENTIVE => 'Maintenance préventive',
            self::TYPE_CORRECTIVE => 'Maintenance corrective',
            self::TYPE_EMERGENCY => 'Intervention d\'urgence',
            self::TYPE_INSPECTION => 'Inspection',
        ];
    }

    public static function getAvailablePriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_MEDIUM => 'Moyenne',
            self::PRIORITY_HIGH => 'Haute',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Programmée',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_COMPLETED => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_POSTPONED => 'Reportée',
        ];
    }

    public static function getUpcomingCount($days = 7): int
    {
        return self::upcoming($days)->count();
    }

    public static function getOverdueCount(): int
    {
        return self::overdue()->count();
    }

    public static function getUrgentCount(): int
    {
        return self::where('priority', self::PRIORITY_URGENT)
                  ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS])
                  ->count();
    }
}