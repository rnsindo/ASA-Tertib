<?php

namespace App\Http\Controllers;

use App\Exports\StudentRoomAssignmentTemplateExport;
use Illuminate\Http\Request;
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

        return Excel::download(new StudentRoomAssignmentTemplateExport(), 'template-data-peserta-ruangan.xlsx');
    }
}
