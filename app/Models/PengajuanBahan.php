<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengajuanBahan extends Model
{
    use HasFactory;

    public function getIdBahan($id)
    {
        // Mengambil hanya kolom id_barang berdasarkan id
        $idBarang = DB::table('pengajuan_bahans')->where('id', $id)->first();

        // Mengirim data ke view
        return $idBarang;
    }

    
    public function getNamaBahan($id)
    {
        // Mengambil hanya kolom id_barang berdasarkan id
        $NamaBarang = DB::table('bahan_olahans')->where('id', $id)->value('nama_bahan');

        // Mengirim data ke view
        return $NamaBarang;
    }

    public function getNamaUser($id)
    {
        // Mengambil hanya kolom id_barang berdasarkan id
        $nama = DB::table('users')->where('id', $id)->value('name');

        // Mengirim data ke view
        return $nama;
    }
}
