<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueCallEvent extends Model
{
    protected $fillable = [
        'queue_session_id',
        'queue_ticket_id',
        'queue_service_id',
        'service_counter_id',
        'called_by',
        'ticket_code',
        'service_name',
        'counter_name',
        'applicant_name',
        'announcement_text',
        'called_at',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(QueueSession::class, 'queue_session_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(QueueTicket::class, 'queue_ticket_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(QueueService::class, 'queue_service_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(ServiceCounter::class, 'service_counter_id');
    }

    public function calledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by');
    }
}
