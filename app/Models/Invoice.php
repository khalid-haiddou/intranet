<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'invoice_number', 'user_id', 'amount', 'tax_amount', 'total_amount',
        'status', 'issued_at', 'due_at', 'paid_at', 'description', 'items',
        'notes', 'payment_method', 'payment_reference', 'payment_notes'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'items' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
            ->orWhere(function($q) {
                $q->where('status', self::STATUS_SENT)
                  ->where('due_at', '<', now());
            });
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('issued_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::latest('id')->first();
        $number = $lastInvoice ? (intval(str_replace('F', '', $lastInvoice->invoice_number)) + 1) : 250607;
        return 'F' . $number;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_SENT => 'En attente',
            self::STATUS_PAID => 'Payée',
            self::STATUS_OVERDUE => 'En retard',
            self::STATUS_CANCELLED => 'Annulée',
            default => 'Payée'
        };
    }
}