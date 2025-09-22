<?php

// app/Models/PengajuanBarang.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengajuanBarang extends Model
{
    use HasFactory;

    public function getIdBarang($id)
    {
        // Mengambil hanya kolom id_barang berdasarkan id
        $idBarang = DB::table('pengajuan_barangs')->where('id', $id)->first();

        // Mengirim data ke view
        return $idBarang;
    }
    public function getNamaBarang($id)
    {
        // Mengambil hanya kolom id_barang berdasarkan id
        $NamaBarang = DB::table('master_barangs')->where('id', $id)->value('nama_barang');

        // Mengirim data ke view
        return $NamaBarang;
    }
}
