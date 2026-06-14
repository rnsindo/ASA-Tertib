<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueTicket extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_CALLED = 'called';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_NO_SHOW = 'no_show';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_TRANSFERRED = 'transferred';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_WAITING => 'Antrian',
        self::STATUS_CALLED => 'Dipanggil',
        self::STATUS_IN_PROGRESS => 'Berlangsung',
        self::STATUS_NO_SHOW => 'Tidak di Tempat',
        self::STATUS_CANCELLED => 'Dibatalkan',
        self::STATUS_TRANSFERRED => 'Dipindahkan',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    protected $fillable = [
        'applicant_id',
        'queue_session_id',
        'queue_service_id',
        'service_counter_id',
        'transferred_from_counter_id',
        'assigned_by',
        'handled_by',
        'queue_date',
        'queue_number',
        'call_sequence',
        'ticket_code',
        'status',
        'assigned_at',
        'called_at',
        'started_at',
        'no_show_at',
        'no_show_count',
        'requeued_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'call_sequence' => 'float',
            'assigned_at' => 'datetime',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'no_show_at' => 'datetime',
            'requeued_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
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

    public function transferredFromCounter(): BelongsTo
    {
        return $this->belongsTo(ServiceCounter::class, 'transferred_from_counter_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
