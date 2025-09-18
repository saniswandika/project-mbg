<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\rekomendasi_terdaftar_dtks as C;
use Illuminate\Support\Facades\DB;
use Faker\Generator as Faker;
use Carbon\Carbon;

class dtksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Menggunakan Query Builder untuk mengambil data dari tabel rekomendasi_terdaftar_dtks
        $dataDtk = DB::table('dtks')->inRandomOrder()->first();
    
        return [
            'Nomor_Surat' => $dataDtk->Nomor_Surat,
            'no_pendaftaran_sudtks' => $dataDtk->no_pendaftaran_sudtks,
            'id_provinsi_sudtks' => $dataDtk->id_provinsi_sudtks,
            'id_kabkot_sudtks' => $dataDtk->id_kabkot_sudtks,
            'id_kecamatan_sudtks' => $dataDtk->id_kecamatan_sudtks,
            'id_kelurahan_sudtks' => $dataDtk->id_kelurahan_sudtks,
            'jenis_pelapor_sudtks' => $dataDtk->jenis_pelapor_sudtks,
            'ada_nik_sudtks' => $dataDtk->ada_nik_sudtks,
            'no_kk_sudtks' => $dataDtk->no_kk_sudtks,
            'nama_sudtks' => $dataDtk->nama_sudtks,
            'tempat_lahir_sudtks' => $dataDtk->tempat_lahir_sudtks,
            'tgl_lahir_sudtks' => Carbon::parse($dataDtk->tgl_lahir_sudtks),
            'jenis_kelamin_sudtks' => $dataDtk->jenis_kelamin_sudtks,
            'telp_sudtks' => mt_rand(1, 10000),
            'email_sudtks' => $dataDtk->email_sudtks,
            'alamat_sudtks' => $dataDtk->alamat_sudtks,
            'status_dtks_sudtks' => $dataDtk->status_dtks_sudtks,
            'file_ktp_terlapor_sudtks' => $dataDtk->file_ktp_terlapor_sudtks,
            'file_kk_terlapor_sudtks' => $dataDtk->file_kk_terlapor_sudtks,
            'file_keterangan_dtks_sudtks' => $dataDtk->file_keterangan_dtks_sudtks,
            'file_pendukung_sudtks' => $dataDtk->file_pendukung_sudtks,
            'tujuan_sudtks' => $dataDtk->tujuan_sudtks,
            'status_aksi_sudtks' => $dataDtk->status_aksi_sudtks,
            'petugas_sudtks' => $dataDtk->petugas_sudtks,
            'createdby_sudtks' => $dataDtk->createdby_sudtks,
            'updatedby_sudtks' => $dataDtk->updatedby_sudtks,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
