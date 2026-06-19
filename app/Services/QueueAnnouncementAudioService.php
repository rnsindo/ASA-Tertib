<?php

namespace App\Services;

use App\Models\QueueCallEvent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QueueAnnouncementAudioService
{
    public function eventAudioUrls(QueueCallEvent $event): array
    {
        $event->loadMissing(['service', 'counter']);

        return collect([
            $this->publicAsset('audio/queue/system/bell.wav'),
            $this->publicAsset('audio/queue/phrases/nomor-antrian.mp3'),
            ...$this->spellCode($event->ticket_code),
            $this->publicAsset('audio/queue/phrases/silakan-menuju.mp3'),
            ...$this->entityAudio(
                $event->counter?->announcement_audio_path,
                $event->counter?->code,
            ),
            $this->publicAsset('audio/queue/phrases/layanan.mp3'),
            ...$this->entityAudio(
                $event->service?->announcement_audio_path,
                $event->service?->code,
            ),
        ])->filter()->values()->all();
    }

    private function entityAudio(?string $path, ?string $fallbackCode): array
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return [Storage::disk('public')->url($path)];
        }

        return $this->spellCode($fallbackCode ?: '');
    }

    private function spellCode(string $code): array
    {
        return collect(str_split(Str::upper($code)))
            ->filter(fn (string $character): bool => preg_match('/[A-Z0-9]/', $character) === 1)
            ->map(function (string $character): string {
                $folder = ctype_digit($character) ? 'digits' : 'letters';

                return $this->publicAsset('audio/queue/' . $folder . '/' . Str::lower($character) . '.mp3');
            })
            ->values()
            ->all();
    }

    private function publicAsset(string $path): string
    {
        return asset($path);
    }
}
