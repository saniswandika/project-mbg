<?php

namespace App\Http\Controllers;

use App\Models\PelaporanPub;
use App\Models\rekomendasi_pelaporan_pub;
use App\Models\rekomendasi_terdaftar_yayasan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class fileRekomendasiController extends Controller
{
    public function FileRekomYayasan($id)
    {
        $rekomendasiTerdaftaryayasan = rekomendasi_terdaftar_yayasan::find($id);
        $pdf = PDF::loadHtml(view('rekomendasi_terdaftar_yayasans.file_permohonan',compact('rekomendasiTerdaftaryayasan')));
        $filename = 'File Permohonan' . $rekomendasiTerdaftaryayasan->nama . '.pdf';
        return $pdf->stream($filename);
    }
    public function downloadFile($file_name)
    {
        $file_path = public_path('Download/Resi-PelaporanPub-pdf' . $file_name);
        return response()->download($file_path);
    }
}
