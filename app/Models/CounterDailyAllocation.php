<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounterDailyAllocation extends Model
{
    protected $fillable = [
        'queue_session_id',
        'queue_service_id',
        'service_counter_id',
        'target_quota',
        'manual_overflow_allowed',
    ];

    protected function casts(): array
    {
        return [
            'manual_overflow_allowed' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(QueueSession::class, 'queue_session_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(QueueService::class, 'queue_service_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(ServiceCounter::class, 'service_counter_id');
    }
}
