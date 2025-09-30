<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Devis extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'devis_number',
        'user_id',
        'client_name',  // ADD THIS
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'issued_at',
        'valid_until',
        'description',
        'items',
        'notes',
        'terms',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'valid_until' => 'datetime',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'items' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get client name - either from manual entry or from user
    public function getClientNameAttribute($value)
    {
        return $value ?? $this->user?->display_name ?? 'Client inconnu';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_SENT => 'Envoyé',
            self::STATUS_ACCEPTED => 'Accepté',
            self::STATUS_REJECTED => 'Refusé',
            self::STATUS_EXPIRED => 'Expiré',
            default => 'Inconnu'
        };
    }

    // Keep this for auto-generation if needed
    public static function generateDevisNumber(): string
    {
        $year = now()->format('y');
        $month = now()->format('m');
        $day = now()->format('d');
        
        $lastDevis = self::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereDay('created_at', now()->day)
            ->latest('id')
            ->first();
        
        $number = $lastDevis ? ((int) substr($lastDevis->devis_number, -2)) + 1 : 1;
        
        return 'D' . $year . $month . $day . str_pad($number, 2, '0', STR_PAD_LEFT);
    }
}