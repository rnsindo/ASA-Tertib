<?php

namespace App\Imports;

use App\Models\StudentRoomAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class StudentRoomAssignmentImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    private int $imported = 0;

    private int $updated = 0;

    private int $skipped = 0;

    /**
     * @var list<string>
     */
    private array $errors = [];

    public function __construct(private readonly ?int $importedBy = null) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $nisn = $this->clean($row['nisn'] ?? null);
            $name = $this->uppercase($row['nama'] ?? null);
            $juniorSchool = $this->uppercase($row['smp'] ?? null);
            $birthDate = $this->parseBirthDate($row['tanggal_lahir'] ?? null);
            $room = $this->uppercase($row['ruangan'] ?? null);

            if ($nisn === '' && $name === '' && $juniorSchool === '' && $room === '') {
                continue;
            }

            $missing = collect([
                'NISN' => $nisn,
                'Nama' => $name,
                'SMP' => $juniorSchool,
                'Tanggal Lahir' => $birthDate,
                'Ruangan' => $room,
            ])->filter(fn (mixed $value): bool => $value === null || $value === '')->keys()->all();

            if ($missing !== []) {
                $this->skip($rowNumber, 'Kolom wajib kosong: ' . implode(', ', $missing) . '.');

                continue;
            }

            if (! preg_match('/^[0-9]{5,20}$/', $nisn)) {
                $this->skip($rowNumber, 'NISN harus angka 5 sampai 20 digit.');

                continue;
            }

            $assignment = StudentRoomAssignment::query()->updateOrCreate(
                ['nisn' => $nisn],
                [
                    'name' => $name,
                    'junior_school' => $juniorSchool,
                    'birth_date' => $birthDate,
                    'room' => $room,
                    'imported_by' => $this->importedBy,
                ],
            );

            $assignment->wasRecentlyCreated ? $this->imported++ : $this->updated++;
        }
    }

    public function summary(): array
    {
        return [
            'imported' => $this->imported,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }

    private function clean(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function uppercase(mixed $value): string
    {
        return Str::upper($this->clean($value));
    }

    private function parseBirthDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }

        $value = trim((string) $value);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
                continue;
            }

            if ($date !== false && $date->format($format) === $value) {
                return $date->toDateString();
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function skip(int $rowNumber, string $reason): void
    {
        $this->skipped++;
        $this->errors[] = 'Baris ' . $rowNumber . ': ' . $reason;
    }
}
