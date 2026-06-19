<?php

namespace App\Http\Controllers;

use App\Exports\StudentRoomAssignmentTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class StudentRoomTemplateController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(
            $request->user()
                && ($request->user()->can('admin.data_peserta_ruangan') || $request->user()->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );

        if (! class_exists(Excel::class)) {
            return Response::make(
                'Package Excel belum tersedia di server. Jalankan composer install --no-dev --optimize-autoloader lalu php artisan optimize:clear.',
                503,
            );
        }

        return Excel::download(new StudentRoomAssignmentTemplateExport(), 'template-data-peserta-ruangan.xlsx');
    }
}
