<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $columns = ['id', 'nama_lengkap', 'nik', 'no_kk', 'alamat']; // Kolom yang ingin ditampilkan

        $totalData = Pegawai::count(); // Total data tanpa filter
        $totalFiltered = $totalData; // Total data yang difilter (bisa berbeda dengan totalData)

        $Pegawai = Pegawai::orderBy('id', 'ASC');

        // Filter berdasarkan pencarian jika ada
        if ($request->has('search') && $request->input('search.value') != '') {
            $search = $request->input('search.value');
            $Pegawai = $Pegawai->where(function($query) use ($search) {
                $query->where('nama_lengkap', 'like', "%$search%")
                    ->orWhere('nik', 'like', "%$search%")
                    ->orWhere('no_kk', 'like', "%$search%")
                    ->orWhere('alamat', 'like', "%$search%");
            });
        }

        // Ambil data berdasarkan limit dan offset (pagination)
        $Pegawai = $Pegawai->offset($request->input('start'))
                    ->limit($request->input('length'))
                    ->get();

        $totalFiltered = $Pegawai->count(); // Filtered count (setelah pencarian)

        // Kembalikan data dalam format yang sesuai dengan DataTables
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $Pegawai
        ]);
    }

}
