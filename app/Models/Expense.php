<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'description',
        'amount',
        'category',
        'vendor',
        'expense_date',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('expense_date', [
            now()->startOfMonth(), 
            now()->endOfMonth()
        ]);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'rent' => 'Loyer',
            'utilities' => 'Charges',
            'maintenance' => 'Maintenance',
            'supplies' => 'Fournitures',
            'equipment' => 'Équipement',
            'salaries' => 'Salaires',
            'marketing' => 'Marketing',
            'insurance' => 'Assurance',
            'other' => 'Autre',
            default => 'Inconnu'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PAID => 'Payée',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_REJECTED => 'Rejetée',
            default => 'Inconnu'
        };
    }

    // Static helpers
    public static function getAvailableCategories(): array
    {
        return [
            'rent' => 'Loyer',
            'utilities' => 'Charges',
            'maintenance' => 'Maintenance',
            'supplies' => 'Fournitures',
            'equipment' => 'Équipement',
            'salaries' => 'Salaires',
            'marketing' => 'Marketing',
            'insurance' => 'Assurance',
            'other' => 'Autre',
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PAID => 'Payée',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_REJECTED => 'Rejetée',
        ];
    }
}