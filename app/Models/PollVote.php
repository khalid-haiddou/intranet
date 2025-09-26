<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'user_id',
        'selected_options',
    ];

    protected $casts = [
        'selected_options' => 'array',
    ];

    // Relationships
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    public function getSelectedOptionsTextAttribute(): array
    {
        $options = [];
        $pollOptions = $this->poll->options;

        foreach ($this->selected_options as $index) {
            if (isset($pollOptions[$index])) {
                $options[] = $pollOptions[$index];
            }
        }

        return $options;
    }
}