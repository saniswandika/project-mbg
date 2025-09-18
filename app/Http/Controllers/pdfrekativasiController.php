<?php

namespace App\Http\Controllers;

use App\Models\rekomendasi_rekativasi_pbi_jk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class pdfrekativasiController extends Controller
{
    public function show($id)

    {   
        $rekomendasi_rekativasi_pbi_jks = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('roles', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', 'roles.id')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'roles.name')
            ->where('rekomendasi_rekativasi_pbi_jks.id', $id)
            ->first();
        $pdf = PDF::loadView('pdfrekativasiview', compact('rekomendasi_rekativasi_pbi_jks'));
        $filename = 'Permohonan Layanan- Rekativasi PBI JK' . $rekomendasi_rekativasi_pbi_jks->nama_pbijk . '.pdf';
        return $pdf->stream($filename);
    }
    public function downloadFile($file_name)
    {
        $file_path = public_path('Download/Resi-Pengaduan-pdf' . $file_name);
        return response()->download($file_path);
    }
}
