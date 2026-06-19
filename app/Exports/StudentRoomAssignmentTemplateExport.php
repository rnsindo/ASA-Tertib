<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentRoomAssignmentTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'NISN',
            'Nama',
            'SMP',
            'Tanggal Lahir',
            'Ruangan',
        ];
    }

    public function array(): array
    {
        return [
            ['1234567890', 'CONTOH NAMA SISWA', 'SMP CONTOH', '06-02-2008', 'RUA1'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A2:A500')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('D2:D500')->getNumberFormat()->setFormatCode('dd-mm-yyyy');

        return [];
    }
}
