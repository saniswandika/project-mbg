<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\rekomendasi_terdaftar_dtks;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class dummyRekomDtks extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Menggunakan Query Builder untuk mengambil data dari tabel dtks dengan batasan 20
        $dtksData = DB::table('dtks')->limit(20)->get();
        foreach ($dtksData as $dataDtk) {
            $province = DB::table('indonesia_provinces')->where('code', '32')->get();
            $kota = DB::table('indonesia_cities')->where('code', '3273')->get();
            $kecamatans = DB::table('indonesia_districts')->where('city_code', '3273')->get();
            $kelurahans = DB::table('indonesia_villages')->where('name_village', $dataDtk->Desa_Kelurahan)->limit(20)->get();
        }
     
        // Menambahkan data ke tabel rekomendasi_terdaftar_dtks
        foreach ($dtksData as $dataDtk) {
            $result = DB::table('dtks')
            ->join('indonesia_provinces', 'dtks.Provinsi', '=', 'indonesia_provinces.name_prov')
            ->join('indonesia_cities', 'dtks.Kabupaten_Kota', '=', 'indonesia_cities.Name_cities')
            ->join('indonesia_districts', 'dtks.Kecamatan', '=', 'indonesia_districts.name_districts')
            ->join('indonesia_villages', 'dtks.Desa_Kelurahan', '=', 'indonesia_villages.name_village')
            ->select(
                // 'indonesia_provinces.name_prov as province_name',
                // 'indonesia_cities.Name_cities as city_name',
                // 'indonesia_districts.name_districts as district_name',
                // 'indonesia_villages.name_village as village_name',
                'indonesia_provinces.code as province_code',
                'indonesia_cities.code as city_code',
                'indonesia_districts.code as district_code',
                'indonesia_villages.code as village_code'

            )
            ->where('dtks.id', $dataDtk->id)
            ->first();
            $username = Str::random(8); // Generate a random string for the username
            $domain = ['@gmail.com']; // Common domain names
            // dd($result);
            rekomendasi_terdaftar_dtks::create([
                // return [
                    'Nomor_Surat' => $username,
                    'no_pendaftaran_sudtks' =>  mt_rand(1, 10000),
                    'id_provinsi_sudtks' => 32,
                    'id_kabkot_sudtks' => 3273,
                    'id_kecamatan_sudtks' => 327306,
                    'id_kelurahan_sudtks' => $result->village_code,
                    'jenis_pelapor_sudtks' => 'Diri Sendiri',
                    'ada_nik_sudtks' => 1,
                    'no_kk_sudtks' => 43242343243,
                    'nama_sudtks' => $dataDtk->Nama,
                    'tempat_lahir_sudtks' => $dataDtk->Tempat_Lahir,
                    'tgl_lahir_sudtks' => Carbon::parse($dataDtk->Tanggal_Lahir),
                    'jenis_kelamin_sudtks' => $dataDtk->Jenis_Kelamin,
                    'telp_sudtks' => mt_rand(1, 10000),
                    // 'email_sudtks' => $domain[array_rand($domain)],
                    'alamat_sudtks' => 'bandung',
                    'status_dtks_sudtks' => 'Terdaftar',
                    // 'file_ktp_terlapor_sudtks' => $dataDtk->file_ktp_terlapor_sudtks,
                    // 'file_kk_terlapor_sudtks' => $dataDtk->file_kk_terlapor_sudtks,
                    // 'file_keterangan_dtks_sudtks' => $dataDtk->file_keterangan_dtks_sudtks,
                    // 'file_pendukung_sudtks' => $dataDtk->file_pendukung_sudtks,
                    'tujuan_sudtks' => 2,
                    'status_aksi_sudtks' => 'Teruskan',
                    'petugas_sudtks' => 2,
                    'createdby_sudtks' => 3,
                    'updatedby_sudtks' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                // ...atribut lainnya
            ]);
        }
    }
}
