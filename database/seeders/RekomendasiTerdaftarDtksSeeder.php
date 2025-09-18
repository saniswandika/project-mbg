<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekomendasiTerdaftarDtksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kelurahans = DB::table('indonesia_villages')
        ->select(
            'indonesia_provinces.code AS provinces_code',
            'indonesia_cities.code AS cities_code',
            'indonesia_villages.code AS village_code',
            'indonesia_districts.code AS district_code'
        )
        ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
        ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
        ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
        ->where('indonesia_cities.code', '3273')
        ->take(1)
        ->get();
        for ($i = 0; $i < 10; $i++) {
            foreach ($kelurahans as $kelurahan) {
                DB::table('rekomendasi_terdaftar_dtks')->insert([
                    // 'created_at' => $kelurahan->created_at,
                    'id_provinsi_sudtks' => $kelurahan->provinces_code,
                    'id_kabkot_sudtks' => $kelurahan->cities_code,
                    'id_kecamatan_sudtks' => $kelurahan->district_code,
                    'id_kelurahan_sudtks' => $kelurahan->village_code,
                    // Masukkan data lainnya sesuai dengan kolom pada tabel
                    'jenis_pelapor_sudtks' => 'Jenis Pelapor', // contoh data
                    'ada_nik_sudtks' => '1', // contoh data
                    'nik_sudtks' => '1234567890', // contoh data
                    'no_kk_sudtks' => '9876543210', // contoh data
                    'nama_sudtks' => 'John Doe', // contoh data
                    'tempat_lahir_sudtks' => 'Jakarta', // contoh data
                    'tgl_lahir_sudtks' => '1990-01-01', // contoh data
                    'jenis_kelamin_sudtks' => 'Laki-laki', // contoh data
                    'telp_sudtks' => '081234567890', // contoh data
                    'alamat_sudtks' => 'Jl. Raya No. 123', // contoh data
                    'file_ktp_terlapor_sudtks' => 'ktp_terlapor.jpg', // contoh data
                    'file_kk_terlapor_sudtks' => 'kk_terlapor.jpg', // contoh data
                    'file_keterangan_dtks_sudtks' => 'keterangan_dtks.pdf', // contoh data
                    'file_pendukung_sudtks' => 'file_pendukung.zip', // contoh data
                    'catatan_sudtks' => 'Catatan mengenai laporan', // contoh data
                    'tujuan_sudtks' => '6', // contoh data
                    'status_aksi_sudtks' => 'Teruskan', // contoh data
                    'petugas_sudtks' => '3', // contoh data
                    'status_dtks_sudtks' => 'Terdaftar', // contoh data
                    'createdby_sudtks' => '6', // contoh data
                    'updatedby_sudtks' => '6', // contoh data
                ]);
            }
        }
    }
}
