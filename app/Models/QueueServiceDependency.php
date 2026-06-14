<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueServiceDependency extends Model
{
    public const MODE_QUEUED = 'queued';
    public const MODE_CALLED = 'called';
    public const MODE_IN_PROGRESS = 'in_progress';
    public const MODE_COMPLETED = 'completed';

    public const MODES = [
        self::MODE_QUEUED,
        self::MODE_CALLED,
        self::MODE_IN_PROGRESS,
        self::MODE_COMPLETED,
    ];

    protected $fillable = [
        'queue_session_id',
        'queue_service_id',
        'required_queue_service_id',
        'required_status_mode',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(QueueService::class, 'queue_service_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(QueueSession::class, 'queue_session_id');
    }

    public function requiredService(): BelongsTo
    {
        return $this->belongsTo(QueueService::class, 'required_queue_service_id');
    }
}
