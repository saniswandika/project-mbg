<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use PDF;

class pdfController extends Controller
{
    public function show($id)
    {
        // $pdf->loadHtml(view('pdf', $data));
        $pengaduan = Pengaduan::find($id);
        $pdf = PDF::loadHtml(view('pdfview',compact('pengaduan')));
        $filename = 'Resi-Pengaduan_' . $pengaduan->nama . '.pdf';
        return $pdf->stream($filename);
    }
    public function downloadFile($file_name)
    {
        $file_path = public_path('Download/Resi-Pengaduan-pdf' . $file_name);
        return response()->download($file_path);
    }
    // public function downloadPDF($id)
    // {
    //     $pengaduans = Pengaduan::find($id);
    //     $pdf = PDF::loadView('pdfview', compact('pengaduans'));
    //     return $pdf->download('pengaduans' . $id . '.pdf');
    // }
}
