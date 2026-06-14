<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceDailyQuota extends Model
{
    protected $fillable = [
        'queue_session_id',
        'queue_service_id',
        'max_daily_quota',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
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
}
