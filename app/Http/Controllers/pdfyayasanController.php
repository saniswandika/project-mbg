<?php

namespace App\Http\Controllers;

use App\Models\rekomendasi_terdaftar_yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class pdfyayasanController extends Controller
{
    public function show($id)
    {
        $rekomendasi_terdaftar_yayasans = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('roles', 'rekomendasi_terdaftar_yayasans.tujuan', '=', 'roles.id')
            ->select('rekomendasi_terdaftar_yayasans.*', 'roles.name')
            ->where('rekomendasi_terdaftar_yayasans.id', $id)
            ->first();
        $pdf = PDF::loadView('pdfyayasanview', compact('rekomendasi_terdaftar_yayasans'));
        $filename = 'Permohonan Layanan- Yayasan' . $rekomendasi_terdaftar_yayasans->nama_pel . '.pdf';
        return $pdf->stream($filename);
    }
    public function downloadFile($file_name)
    {
        $file_path = public_path('Download/Resi-Pengaduan-pdf' . $file_name);
        return response()->download($file_path);
    }
}
