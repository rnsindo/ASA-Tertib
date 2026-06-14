<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCheckin extends Model
{
    public const STATUS_CHECKED_IN = 'checked_in';

    public const METHOD_QR = 'qr';
    public const METHOD_OFFICER = 'officer';

    protected $fillable = [
        'queue_session_id',
        'applicant_id',
        'queue_session_qr_code_id',
        'presence_status',
        'presence_confirmed_at',
        'presence_confirmed_by',
        'presence_method',
        'presence_location_code',
        'presence_notes',
    ];

    protected function casts(): array
    {
        return [
            'presence_confirmed_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(QueueSession::class, 'queue_session_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QueueSessionQrCode::class, 'queue_session_qr_code_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'presence_confirmed_by');
    }
}
