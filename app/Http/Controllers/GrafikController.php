<?php

namespace App\Http\Controllers;

use App\Models\rekomendasi_bantuan_pendidikan;
use App\Models\rekomendasi_terdaftar_dtks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GrafikController extends Controller
{
    public function GrafikDtks($name)
    {
        // Ambil data dari database
       

        $kelurahans = DB::table('indonesia_villages')
            ->select('rekomendasi_terdaftar_dtks.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            // ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->get();
        
        $dataKelurahanDtks = DB::table('indonesia_villages')
            ->select( 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            // ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->get();
        // dd($kelurahans);
        $lokasiDataDtks = $dataKelurahanDtks->first();
        // dd($lokasiDataDtks);
        //data PerHari Berdasarkan Minggu
            // Ubah hasil query menjadi array
            $kelurahanData = $kelurahans->toArray();

            // Inisialisasi array untuk menyimpan jumlah data per hari dalam seminggu berdasarkan minggu dan bulan
            $dataPerHariBerdasarkanPerMinggu = array();

            // Array dengan nama-nama hari dalam bahasa Indonesia
            $namaHariIndonesia = array(
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
            );

            // Ambil minggu sekarang dalam format ISO-8601 (W atau week number)
            $mingguSekarang = date('W');
            // dd($mingguSekarang);
            // Loop melalui data yang didapatkan
            foreach ($kelurahanData as $kelurahan) {
                // Ambil tanggal dari kolom 'created_at'
                $created_at = $kelurahan->created_at;

                // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                $createdAt = \Carbon\Carbon::parse($created_at);

                // Pastikan tanggal berada dalam minggu yang sama dengan minggu sekarang
                if ($createdAt->weekOfYear == $mingguSekarang) {
                    // Set lokal bahasa Indonesia
                    $createdAt->setLocale('id_ID');

                    // Ambil informasi minggu dan tahun dari tanggal
                    $weekNumber = $createdAt->weekOfMonth;
                    $yearMonth = $createdAt->format('F Y');

                    // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                    $weekIdentity = "Minggu ke-" . $weekNumber . " " . $yearMonth;

                    // Peroleh nama hari dalam bahasa Indonesia dari tanggal
                    $dayName = $createdAt->formatLocalized('%A');

                    // Ubah nama hari menjadi bahasa Indonesia menggunakan array yang telah dibuat sebelumnya
                    $dayNameIndonesia = $namaHariIndonesia[$dayName];

                    // Buat identitas unik untuk setiap hari dalam minggu (misal: "Minggu ke-2 Hari Senin Juli 2023")
                    $dayIdentity = $weekIdentity . " Hari " . $dayNameIndonesia;

                    // Tambahkan atau tambahkan jumlah data per hari dalam seminggu ke array
                    if (isset($dataPerHariBerdasarkanPerMinggu[$dayIdentity])) {
                        $dataPerHariBerdasarkanPerMinggu[$dayIdentity]++;
                    } else {
                        $dataPerHariBerdasarkanPerMinggu[$dayIdentity] = 1;
                    }
                }
            }
            // dd($dataPerHariBerdasarkanPerMinggu);
        //Data PerMinggu Berdasarkan Bulan
            // Inisialisasi array untuk menyimpan jumlah data per minggu dalam bulan
            $dataPerMingguBulan = array();

            // Loop melalui data yang didapatkan
            foreach ($kelurahans as $kelurahan) {
                // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                $createdAt = Carbon::parse($kelurahan->created_at);
                
                // Ambil informasi minggu dan tahun dari tanggal
                $weekNumber = $createdAt->weekOfMonth;
                $yearMonth = $createdAt->format('F Y');
                
                // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                $weekIdentity = "Minggu ke-" . $weekNumber;
                
                // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
                if (isset($dataPerMingguBulan[$weekIdentity])) {
                    $dataPerMingguBulan[$weekIdentity]++;
                } else {
                    $dataPerMingguBulan[$weekIdentity] = 1;
                }
            }
        //Data PerBulan Berdasarkan Tahun
            $dataPerBulan = array();

            // Loop melalui data yang didapatkan
            foreach ($kelurahans as $kelurahan) {
                // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                $createdAt = Carbon::parse($kelurahan->created_at);

                // Ambil informasi bulan-tahun dari tanggal
                $yearMonth = $createdAt->format('F');

                // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
                $monthIdentity = $yearMonth;

                // Tambahkan atau tambahkan jumlah data per bulan ke array
                if (isset($dataPerBulan[$monthIdentity])) {
                    $dataPerBulan[$monthIdentity]++;
                } else {
                    $dataPerBulan[$monthIdentity] = 1;
                }
            }

        $statusSelesai = DB::table('indonesia_villages')
            ->select('rekomendasi_terdaftar_dtks.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Selesai')
            // ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->count();
        // dd($statusSelesai);
        // $dataPerMingguBulanSelesai = array();

        // // data selesaai pper minggu
        // foreach ($statusSelesai as $statusSelesais) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);
            
        //     // Ambil informasi minggu dan tahun dari tanggal
        //     $NomerPerMinggu = $createdAt->weekOfMonth;
        //     $yearMonth = $createdAt->format('F Y');
            
        //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
        //     $IndentitasSelsaiPerMinggu = "Minggu ke-" . $NomerPerMinggu;
            
        //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
        //     if (isset($dataPerMingguBulanSelesai[$IndentitasSelsaiPerMinggu])) {
        //         $dataPerMingguBulanSelesai[$IndentitasSelsaiPerMinggu]++;
        //     } else {
        //         $dataPerMingguBulanSelesai[$IndentitasSelsaiPerMinggu] = 1;
        //     }
        // }

        // //data selesai per bulan
        // $dataPerBulanSelesai = array();

        // // Loop melalui data yang didapatkan
        // foreach ($statusSelesai as $statusSelesais) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);

        //     // Ambil informasi bulan-tahun dari tanggal
        //     $yearMonthBulan = $createdAt->format('F');

        //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
        //     $monthIdentityBulan = $yearMonthBulan;

        //     // Tambahkan atau tambahkan jumlah data per bulan ke array
        //     if (isset($dataPerBulanSelesai[$monthIdentityBulan])) {
        //         $dataPerBulanSelesai[$monthIdentityBulan]++;
        //     } else {
        //         $dataPerBulanSelesai[$monthIdentityBulan] = 1;
        //     }
        // }

        //data proses
        $statusProses = DB::table('indonesia_villages')
            ->select('rekomendasi_terdaftar_dtks.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Teruskan')
            // ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->count();
        // dd($statusProses);s
        // // data selesaai pper minggu
        // $dataPerMingguBulanTeruskan = array();
        // foreach ($statusProses as $statusProsess) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);
            
        //     // Ambil informasi minggu dan tahun dari tanggal
        //     $NomerPerMinggu = $createdAt->weekOfMonth;
        //     $yearMonth = $createdAt->format('F Y');
            
        //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
        //     $IndentitasTeruskanPerMinggu = "Minggu ke-" . $NomerPerMinggu;
            
        //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
        //     if (isset($dataPerMingguBulanTeruskan[$IndentitasTeruskanPerMinggu])) {
        //         $dataPerMingguBulanTeruskan[$IndentitasTeruskanPerMinggu]++;
        //     } else {
        //         $dataPerMingguBulanTeruskan[$IndentitasTeruskanPerMinggu] = 1;
        //     }
        // }

        // //data Teruskan per bulan
        // $dataPerBulanTeruskan = array();

        // // Loop melalui data yang didapatkan
        // foreach ($statusProses as $statusProsess) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);

        //     // Ambil informasi bulan-tahun dari tanggal
        //     $yearMonthBulanTeruskan = $createdAt->format('F');

        //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
        //     $monthIdentityBulanTeruskan = $yearMonthBulanTeruskan;

        //     // Tambahkan atau tambahkan jumlah data per bulan ke array
        //     if (isset($dataPerBulanTeruskan[$monthIdentityBulanTeruskan])) {
        //         $dataPerBulanTeruskan[$monthIdentityBulanTeruskan]++;
        //     } else {
        //         $dataPerBulanTeruskan[$monthIdentityBulanTeruskan] = 1;
        //     }
        // }

        //dari sini sudah mulai bantuan pendidikan 
            $kelurahansBantuanPendidikan = DB::table('indonesia_villages')
                ->select('rekomendasi_bantuan_pendidikans.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
                ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
                ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
                ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
                ->join('rekomendasi_bantuan_pendidikans', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->where('indonesia_cities.code', '3273')
                ->where('indonesia_villages.name_village', $name)
                ->get();
            // dd($kelurahansBantuanPendidikan);
            //data PerHari Berdasarkan Minggu
                // Ubah hasil query menjadi array
                $kelurahanDataBantuanPendidikan = $kelurahansBantuanPendidikan->toArray();
                // dd($kelurahanDataBantuanPendidikan);

                // Inisialisasi array untuk menyimpan jumlah data per hari dalam seminggu berdasarkan minggu dan bulan
                $dataPerHariBerdasarkanPerMingguBantuanPendidikan = array();

                // Array dengan nama-nama hari dalam bahasa Indonesia
                $namaHariIndonesiaBantuanPendidikan = array(
                    'Sunday' => 'Minggu',
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                );

                // Ambil minggu sekarang dalam format ISO-8601 (W atau week number)
                $mingguSekarangBantuanPendidikan = date('W');
                // dd($mingguSekarang);
                // Loop melalui data yang didapatkan
                foreach ($kelurahanDataBantuanPendidikan as $kelurahanBantuanPendidikan) {
                    // Ambil tanggal dari kolom 'created_at'
                    $created_at = $kelurahanBantuanPendidikan->created_at;

                    // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                    $createdAt = \Carbon\Carbon::parse($created_at);

                    // Pastikan tanggal berada dalam minggu yang sama dengan minggu sekarang
                    if ($createdAt->weekOfYear == $mingguSekarang) {
                        // Set lokal bahasa Indonesia
                        $createdAt->setLocale('id_ID');

                        // Ambil informasi minggu dan tahun dari tanggal
                        $weekNumber = $createdAt->weekOfMonth;
                        $yearMonth = $createdAt->format('F Y');

                        // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                        $weekIdentity = "Minggu ke-" . $weekNumber . " " . $yearMonth;

                        // Peroleh nama hari dalam bahasa Indonesia dari tanggal
                        $dayName = $createdAt->formatLocalized('%A');

                        // Ubah nama hari menjadi bahasa Indonesia menggunakan array yang telah dibuat sebelumnya
                        $dayNameIndonesia = $namaHariIndonesiaBantuanPendidikan[$dayName];

                        // Buat identitas unik untuk setiap hari dalam minggu (misal: "Minggu ke-2 Hari Senin Juli 2023")
                        $dayIdentity = $weekIdentity . " Hari " . $dayNameIndonesia;

                        // Tambahkan atau tambahkan jumlah data per hari dalam seminggu ke array
                        if (isset($dataPerHariBerdasarkanPerMingguBantuanPendidikan[$dayIdentity])) {
                            $dataPerHariBerdasarkanPerMingguBantuanPendidikan[$dayIdentity]++;
                        } else {
                            $dataPerHariBerdasarkanPerMingguBantuanPendidikan[$dayIdentity] = 1;
                        }
                    }
                }
                // dd($dataPerHariBerdasarkanPerMingguBantuanPendidikan);
            //Data PerMinggu Berdasarkan Bulan
                // Inisialisasi array untuk menyimpan jumlah data per minggu dalam bulan
                $dataPerMingguBulanBantuanPendidikan = array();

                // Loop melalui data yang didapatkan
                foreach ($kelurahanDataBantuanPendidikan as $kelurahanDataBantuanPendidikan) {
                    // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                    $createdAt = Carbon::parse($kelurahanDataBantuanPendidikan->created_at);
                    
                    // Ambil informasi minggu dan tahun dari tanggal
                    $weekNumber = $createdAt->weekOfMonth;
                    $yearMonth = $createdAt->format('F Y');
                    
                    // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                    $weekIdentity = "Minggu ke-" . $weekNumber;
                    
                    // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
                    if (isset($dataPerMingguBulanBantuanPendidikan[$weekIdentity])) {
                        $dataPerMingguBulanBantuanPendidikan[$weekIdentity]++;
                    } else {
                        $dataPerMingguBulanBantuanPendidikan[$weekIdentity] = 1;
                    }
                }
            // dd($dataPerMingguBulanBantuanPendidikan);
            //Data PerBulan Berdasarkan Tahun
                $dataPerBulanBantuanPendidikan = array();
                // dd($kelurahanDataBantuanPendidikan);
                // Loop melalui data yang didapatkan
                // foreach ($kelurahanDataBantuanPendidikan as $kelurahanDataBantuanPendidikans) {
                //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                //     $created_at = $kelurahanDataBantuanPendidikans->created_at;

                //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                //     $createdAt = \Carbon\Carbon::parse($created_at);
                //     // $createdAt = Carbon::parse($kelurahanDataBantuanPendidikans->created_at);

                //     // Ambil informasi bulan-tahun dari tanggal
                //     $yearMonth = $createdAt->format('F');

                //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
                //     $monthIdentity = $yearMonth;

                //     // Tambahkan atau tambahkan jumlah data per bulan ke array
                //     if (isset($dataPerBulanBantuanPendidikan[$monthIdentity])) {
                //         $dataPerBulanBantuanPendidikan[$monthIdentity]++;
                //     } else {
                //         $dataPerBulanBantuanPendidikan[$monthIdentity] = 1;
                //     }
                // }
            // dd($dataPerBulanBantuanPendidikan);
            $statusSelesaiBantuanPendidikan = DB::table('indonesia_villages')
                ->select('rekomendasi_bantuan_pendidikans.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
                ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
                ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
                ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
                ->join('rekomendasi_bantuan_pendidikans', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Selesai')
                ->where('indonesia_cities.code', '3273')
                ->where('indonesia_villages.name_village', $name)
                ->count();
            
            // // dd($statusSelesaiBantuanPendidikan);
            // $dataPerMingguBulanSelesaiBantuanPendidikan = array();

            // // data selesaai pper minggu
            // foreach ($statusSelesaiBantuanPendidikan as $statusSelesaiBantuanPendidikans) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);
                
            //     // Ambil informasi minggu dan tahun dari tanggal
            //     $NomerPerMinggu = $createdAt->weekOfMonth;
            //     $yearMonth = $createdAt->format('F Y');
                
            //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
            //     $IndentitasSelsaiPerMinggu = "Minggu ke-" . $NomerPerMinggu;
                
            //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
            //     if (isset($dataPerMingguBulanSelesaiBantuanPendidikan[$IndentitasSelsaiPerMinggu])) {
            //         $dataPerMingguBulanSelesaiBantuanPendidikan[$IndentitasSelsaiPerMinggu]++;
            //     } else {
            //         $dataPerMingguBulanSelesaiBantuanPendidikan[$IndentitasSelsaiPerMinggu] = 1;
            //     }
            // }
            // // dd($dataPerMingguBulanSelesaiBantuanPendidikan);
            // //data selesai per bulan
            // $dataPerBulanSelesaiBantuanPendidika = array();

            // // Loop melalui data yang didapatkan
            // foreach ($statusSelesaiBantuanPendidikan as $statusSelesaiBantuanPendidikans) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);

            //     // Ambil informasi bulan-tahun dari tanggal
            //     $yearMonthBulan = $createdAt->format('F');

            //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
            //     $monthIdentityBulan = $yearMonthBulan;

            //     // Tambahkan atau tambahkan jumlah data per bulan ke array
            //     if (isset($dataPerBulanSelesaiBantuanPendidika[$monthIdentityBulan])) {
            //         $dataPerBulanSelesaiBantuanPendidika[$monthIdentityBulan]++;
            //     } else {
            //         $dataPerBulanSelesaiBantuanPendidika[$monthIdentityBulan] = 1;
            //     }
            // }
            // dd($dataPerBulanSelesaiBantuanPendidika);
            //data proses
            $statusProsesBantuanPendidikan = DB::table('indonesia_villages')
                ->select('rekomendasi_bantuan_pendidikans.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
                ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
                ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
                ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
                ->join('rekomendasi_bantuan_pendidikans', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Teruskan')
                ->where('indonesia_cities.code', '3273')
                ->where('indonesia_villages.name_village', $name)
                ->count();
            // dd($statusProsesBantuanPendidikan);
            // dd($statusProsesBantuanPendidikan);
            // // data selesaai pper minggu
            // $dataPerMingguBulanTeruskanBantuanPendidikan = array();
            // foreach ($statusProsesBantuanPendidikan as $statusProsesBantuanPendidikans) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);
                
            //     // Ambil informasi minggu dan tahun dari tanggal
            //     $NomerPerMinggu = $createdAt->weekOfMonth;
            //     $yearMonth = $createdAt->format('F Y');
                
            //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
            //     $IndentitasTeruskanPerMinggu = "Minggu ke-" . $NomerPerMinggu;
                
            //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
            //     if (isset($dataPerMingguBulanTeruskanBantuanPendidikan[$IndentitasTeruskanPerMinggu])) {
            //         $dataPerMingguBulanTeruskanBantuanPendidikan[$IndentitasTeruskanPerMinggu]++;
            //     } else {
            //         $dataPerMingguBulanTeruskanBantuanPendidikan[$IndentitasTeruskanPerMinggu] = 1;
            //     }
            // }
            // // dd($dataPerMingguBulanTeruskanBantuanPendidikan);
            // //data Teruskan per bulan
            // $dataPerBulanTeruskanBantuanPendidikan = array();

            // // Loop melalui data yang didapatkan
            // foreach ($statusProses as $statusProsess) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);

            //     // Ambil informasi bulan-tahun dari tanggal
            //     $yearMonthBulanTeruskan = $createdAt->format('F');

            //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
            //     $monthIdentityBulanTeruskan = $yearMonthBulanTeruskan;

            //     // Tambahkan atau tambahkan jumlah data per bulan ke array
            //     if (isset($dataPerBulanTeruskanBantuanPendidikan[$monthIdentityBulanTeruskan])) {
            //         $dataPerBulanTeruskanBantuanPendidikan[$monthIdentityBulanTeruskan]++;
            //     } else {
            //         $dataPerBulanTeruskanBantuanPendidikan[$monthIdentityBulanTeruskan] = 1;
            //     }
            // }
        // dd($dataPerBulanBantuanPendidikan);
        //sampai sini 

        $dataPerHariBerdasarkanPerMingguBantuanPendidikans = json_encode($dataPerHariBerdasarkanPerMingguBantuanPendidikan);
        $dataPerMingguBulans = json_encode($dataPerMingguBulan);
        $dataPerBulanJson = json_encode($dataPerBulan);
        // $dataPerMingguBulanSelesai = json_encode($dataPerMingguBulanSelesai);
        $dataPerHariBerdasarkanPerMinggu = json_encode($dataPerHariBerdasarkanPerMinggu);
        $dataPerMingguBulanBantuanPendidikan = json_encode($dataPerMingguBulanBantuanPendidikan);
        $dataPerBulanBantuanPendidikan = json_encode($dataPerBulanBantuanPendidikan);
        // $dataPerMingguBulanSelesaiBantuanPendidikan = json_encode($dataPerMingguBulanSelesaiBantuanPendidikan);
        // $dataPerHariBerdasarkanPerMingguBantuanPendidikan = json_encode($dataPerHariBerdasarkanPerMingguBantuanPendidikan);
        // dd($dataPerBulanJson);
        // dd($dataPerBulanBantuanPendidikan);
        return view('grafik.index',compact('dataPerMingguBulans',
                                            'dataPerBulanJson',
                                            'lokasiDataDtks',
                                            'dataPerHariBerdasarkanPerMinggu',
                                            'statusProses',
                                            'statusSelesai',
                                            'dataPerMingguBulanBantuanPendidikan',
                                            'dataPerBulanBantuanPendidikan',
                                            'statusProsesBantuanPendidikan',
                                            'statusSelesaiBantuanPendidikan',
                                            // 'dataPerMingguBulanSelesaiBantuanPendidikan',
                                            'dataPerHariBerdasarkanPerMingguBantuanPendidikans'));
        
    }
    public function DraftBantuanSudtksGrafik(Request $request, $name)
    {
        $query = DB::table('rekomendasi_terdaftar_dtks')
        ->leftJoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
        // ->leftJoin('roles', 'users.id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
        ->leftJoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
        ->leftJoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
        ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts', 'users.name')
        ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Draft')
        ->where('b.name_village', $name);
        // dd($query);
            // ->Where(function ($query) use ($name) {
            // ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Draft')
            // ->where('b.name_village', $name);
            
                // $query->where('rekomendasi_terdaftar_dtks.createdby_sudtks',  Auth::user()->id);
            // });
        // dd($query);
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_dtks::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function ProsestBantuanSudtksGrafik(Request $request, $name)
    {
        $query = DB::table('rekomendasi_terdaftar_dtks')
        ->leftJoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
        // ->leftJoin('roles', 'users.id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
        ->leftJoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
        ->leftJoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
        ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts', 'users.name')
        ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Teruskan')
        ->where('b.name_village', $name);
        // dd($query);
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_dtks::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function SelesaiBantuanSudtksGrafik(Request $request, $name)
    {
        $query = DB::table('rekomendasi_terdaftar_dtks')
        ->leftJoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
        // ->leftJoin('roles', 'users.id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
        ->leftJoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
        ->leftJoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
        ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts', 'users.name')
        ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Selesai')
        ->where('b.name_village', $name);
        // dd($query);
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_dtks::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function GrafikBantuanPendidikan($name)
    {
        // Ambil data dari database
        $kelurahans = DB::table('indonesia_villages')
            ->select('rekomendasi_terdaftar_dtks.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->get();
     
        //data PerHari Berdasarkan Minggu
            // Ubah hasil query menjadi array
            $kelurahanData = $kelurahans->toArray();

            // Inisialisasi array untuk menyimpan jumlah data per hari dalam seminggu berdasarkan minggu dan bulan
            $dataPerHariBerdasarkanPerMinggu = array();

            // Array dengan nama-nama hari dalam bahasa Indonesia
            $namaHariIndonesia = array(
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
            );

            // Ambil minggu sekarang dalam format ISO-8601 (W atau week number)
            $mingguSekarang = date('W');
            // dd($mingguSekarang);
            // Loop melalui data yang didapatkan
            foreach ($kelurahanData as $kelurahan) {
                // Ambil tanggal dari kolom 'created_at'
                $created_at = $kelurahan->created_at;

                // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                $createdAt = \Carbon\Carbon::parse($created_at);

                // Pastikan tanggal berada dalam minggu yang sama dengan minggu sekarang
                if ($createdAt->weekOfYear == $mingguSekarang) {
                    // Set lokal bahasa Indonesia
                    $createdAt->setLocale('id_ID');

                    // Ambil informasi minggu dan tahun dari tanggal
                    $weekNumber = $createdAt->weekOfMonth;
                    $yearMonth = $createdAt->format('F Y');

                    // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                    $weekIdentity = "Minggu ke-" . $weekNumber . " " . $yearMonth;

                    // Peroleh nama hari dalam bahasa Indonesia dari tanggal
                    $dayName = $createdAt->formatLocalized('%A');

                    // Ubah nama hari menjadi bahasa Indonesia menggunakan array yang telah dibuat sebelumnya
                    $dayNameIndonesia = $namaHariIndonesia[$dayName];

                    // Buat identitas unik untuk setiap hari dalam minggu (misal: "Minggu ke-2 Hari Senin Juli 2023")
                    $dayIdentity = $weekIdentity . " Hari " . $dayNameIndonesia;

                    // Tambahkan atau tambahkan jumlah data per hari dalam seminggu ke array
                    if (isset($dataPerHariBerdasarkanPerMinggu[$dayIdentity])) {
                        $dataPerHariBerdasarkanPerMinggu[$dayIdentity]++;
                    } else {
                        $dataPerHariBerdasarkanPerMinggu[$dayIdentity] = 1;
                    }
                }
            }
            // dd($dataPerHariBerdasarkanPerMinggu);
        //Data PerMinggu Berdasarkan Bulan
            // Inisialisasi array untuk menyimpan jumlah data per minggu dalam bulan
            $dataPerMingguBulan = array();

            // Loop melalui data yang didapatkan
            foreach ($kelurahans as $kelurahan) {
                // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                $createdAt = Carbon::parse($kelurahan->created_at);
                
                // Ambil informasi minggu dan tahun dari tanggal
                $weekNumber = $createdAt->weekOfMonth;
                $yearMonth = $createdAt->format('F Y');
                
                // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                $weekIdentity = "Minggu ke-" . $weekNumber;
                
                // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
                if (isset($dataPerMingguBulan[$weekIdentity])) {
                    $dataPerMingguBulan[$weekIdentity]++;
                } else {
                    $dataPerMingguBulan[$weekIdentity] = 1;
                }
            }
        //Data PerBulan Berdasarkan Tahun
            $dataPerBulan = array();

            // Loop melalui data yang didapatkan
            foreach ($kelurahans as $kelurahan) {
                // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                $createdAt = Carbon::parse($kelurahan->created_at);

                // Ambil informasi bulan-tahun dari tanggal
                $yearMonth = $createdAt->format('F');

                // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
                $monthIdentity = $yearMonth;

                // Tambahkan atau tambahkan jumlah data per bulan ke array
                if (isset($dataPerBulan[$monthIdentity])) {
                    $dataPerBulan[$monthIdentity]++;
                } else {
                    $dataPerBulan[$monthIdentity] = 1;
                }
            }

        $statusSelesai = DB::table('indonesia_villages')
            ->select('rekomendasi_terdaftar_dtks.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Selesai')
            ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->count();
        // dd($statusSelesai);
        // $dataPerMingguBulanSelesai = array();

        // // data selesaai pper minggu
        // foreach ($statusSelesai as $statusSelesais) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);
            
        //     // Ambil informasi minggu dan tahun dari tanggal
        //     $NomerPerMinggu = $createdAt->weekOfMonth;
        //     $yearMonth = $createdAt->format('F Y');
            
        //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
        //     $IndentitasSelsaiPerMinggu = "Minggu ke-" . $NomerPerMinggu;
            
        //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
        //     if (isset($dataPerMingguBulanSelesai[$IndentitasSelsaiPerMinggu])) {
        //         $dataPerMingguBulanSelesai[$IndentitasSelsaiPerMinggu]++;
        //     } else {
        //         $dataPerMingguBulanSelesai[$IndentitasSelsaiPerMinggu] = 1;
        //     }
        // }

        // //data selesai per bulan
        // $dataPerBulanSelesai = array();

        // // Loop melalui data yang didapatkan
        // foreach ($statusSelesai as $statusSelesais) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);

        //     // Ambil informasi bulan-tahun dari tanggal
        //     $yearMonthBulan = $createdAt->format('F');

        //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
        //     $monthIdentityBulan = $yearMonthBulan;

        //     // Tambahkan atau tambahkan jumlah data per bulan ke array
        //     if (isset($dataPerBulanSelesai[$monthIdentityBulan])) {
        //         $dataPerBulanSelesai[$monthIdentityBulan]++;
        //     } else {
        //         $dataPerBulanSelesai[$monthIdentityBulan] = 1;
        //     }
        // }

        //data proses
        $statusProses = DB::table('indonesia_villages')
            ->select('rekomendasi_terdaftar_dtks.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Teruskan')
            ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->count();
        // dd($statusProses);

        // // data selesaai pper minggu
        // $dataPerMingguBulanTeruskan = array();
        // foreach ($statusProses as $statusProsess) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);
            
        //     // Ambil informasi minggu dan tahun dari tanggal
        //     $NomerPerMinggu = $createdAt->weekOfMonth;
        //     $yearMonth = $createdAt->format('F Y');
            
        //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
        //     $IndentitasTeruskanPerMinggu = "Minggu ke-" . $NomerPerMinggu;
            
        //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
        //     if (isset($dataPerMingguBulanTeruskan[$IndentitasTeruskanPerMinggu])) {
        //         $dataPerMingguBulanTeruskan[$IndentitasTeruskanPerMinggu]++;
        //     } else {
        //         $dataPerMingguBulanTeruskan[$IndentitasTeruskanPerMinggu] = 1;
        //     }
        // }

        // //data Teruskan per bulan
        // $dataPerBulanTeruskan = array();

        // // Loop melalui data yang didapatkan
        // foreach ($statusProses as $statusProsess) {
        //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
        //     $createdAt = Carbon::parse($kelurahan->created_at);

        //     // Ambil informasi bulan-tahun dari tanggal
        //     $yearMonthBulanTeruskan = $createdAt->format('F');

        //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
        //     $monthIdentityBulanTeruskan = $yearMonthBulanTeruskan;

        //     // Tambahkan atau tambahkan jumlah data per bulan ke array
        //     if (isset($dataPerBulanTeruskan[$monthIdentityBulanTeruskan])) {
        //         $dataPerBulanTeruskan[$monthIdentityBulanTeruskan]++;
        //     } else {
        //         $dataPerBulanTeruskan[$monthIdentityBulanTeruskan] = 1;
        //     }
        // }

        //dari sini sudah mulai bantuan pendidikan 
            $kelurahansBantuanPendidikan = DB::table('indonesia_villages')
                ->select('rekomendasi_bantuan_pendidikans.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
                ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
                ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
                ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
                ->join('rekomendasi_bantuan_pendidikans', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->where('indonesia_cities.code', '3273')
                ->where('indonesia_villages.name_village', $name)
                ->get();
            $dataKelurahanBantuanPendidikan = DB::table('indonesia_villages')
            ->select( 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            // ->join('rekomendasi_terdaftar_dtks', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->where('indonesia_cities.code', '3273')
            ->where('indonesia_villages.name_village', $name)
            ->get();
            // dd($kelurahansBantuanPendidikan);
            $lokasiData = $dataKelurahanBantuanPendidikan->first();
            
            //data PerHari Berdasarkan Minggu
                // Ubah hasil query menjadi array
                $kelurahanDataBantuanPendidikan = $kelurahansBantuanPendidikan->toArray();
                // dd($kelurahanDataBantuanPendidikan);

                // Inisialisasi array untuk menyimpan jumlah data per hari dalam seminggu berdasarkan minggu dan bulan
                $dataPerHariBerdasarkanPerMingguBantuanPendidikan = array();

                // Array dengan nama-nama hari dalam bahasa Indonesia
                $namaHariIndonesiaBantuanPendidikan = array(
                    'Sunday' => 'Minggu',
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                );

                // Ambil minggu sekarang dalam format ISO-8601 (W atau week number)
                $mingguSekarangBantuanPendidikan = date('W');
                // dd($mingguSekarang);
                // Loop melalui data yang didapatkan
                foreach ($kelurahanDataBantuanPendidikan as $kelurahanBantuanPendidikan) {
                    // Ambil tanggal dari kolom 'created_at'
                    $created_at = $kelurahanBantuanPendidikan->created_at;

                    // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                    $createdAt = \Carbon\Carbon::parse($created_at);

                    // Pastikan tanggal berada dalam minggu yang sama dengan minggu sekarang
                    if ($createdAt->weekOfYear == $mingguSekarang) {
                        // Set lokal bahasa Indonesia
                        $createdAt->setLocale('id_ID');

                        // Ambil informasi minggu dan tahun dari tanggal
                        $weekNumber = $createdAt->weekOfMonth;
                        $yearMonth = $createdAt->format('F Y');

                        // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                        $weekIdentity = "Minggu ke-" . $weekNumber . " " . $yearMonth;

                        // Peroleh nama hari dalam bahasa Indonesia dari tanggal
                        $dayName = $createdAt->formatLocalized('%A');

                        // Ubah nama hari menjadi bahasa Indonesia menggunakan array yang telah dibuat sebelumnya
                        $dayNameIndonesia = $namaHariIndonesiaBantuanPendidikan[$dayName];

                        // Buat identitas unik untuk setiap hari dalam minggu (misal: "Minggu ke-2 Hari Senin Juli 2023")
                        $dayIdentity = $weekIdentity . " Hari " . $dayNameIndonesia;

                        // Tambahkan atau tambahkan jumlah data per hari dalam seminggu ke array
                        if (isset($dataPerHariBerdasarkanPerMingguBantuanPendidikan[$dayIdentity])) {
                            $dataPerHariBerdasarkanPerMingguBantuanPendidikan[$dayIdentity]++;
                        } else {
                            $dataPerHariBerdasarkanPerMingguBantuanPendidikan[$dayIdentity] = 1;
                        }
                    }
                }
                // dd($dataPerHariBerdasarkanPerMingguBantuanPendidikan);
            //Data PerMinggu Berdasarkan Bulan
                // Inisialisasi array untuk menyimpan jumlah data per minggu dalam bulan
                $dataPerMingguBulanBantuanPendidikan = array();

                // Loop melalui data yang didapatkan
                foreach ($kelurahanDataBantuanPendidikan as $kelurahanDataBantuanPendidikan) {
                    // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                    $createdAt = Carbon::parse($kelurahanDataBantuanPendidikan->created_at);
                    
                    // Ambil informasi minggu dan tahun dari tanggal
                    $weekNumber = $createdAt->weekOfMonth;
                    $yearMonth = $createdAt->format('F Y');
                    
                    // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
                    $weekIdentity = "Minggu ke-" . $weekNumber;
                    
                    // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
                    if (isset($dataPerMingguBulanBantuanPendidikan[$weekIdentity])) {
                        $dataPerMingguBulanBantuanPendidikan[$weekIdentity]++;
                    } else {
                        $dataPerMingguBulanBantuanPendidikan[$weekIdentity] = 1;
                    }
                }
            // dd($dataPerMingguBulanBantuanPendidikan);
            //Data PerBulan Berdasarkan Tahun
                $dataPerBulanBantuanPendidikan = array();

                // Loop melalui data yang didapatkan
                foreach ($kelurahansBantuanPendidikan as $kelurahanDataBantuanPendidikans) {
                    // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
                    $createdAt = Carbon::parse($kelurahanDataBantuanPendidikans->created_at);

                    // Ambil informasi bulan-tahun dari tanggal
                    $yearMonth = $createdAt->format('F');

                    // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
                    $monthIdentity = $yearMonth;

                    // Tambahkan atau tambahkan jumlah data per bulan ke array
                    if (isset($dataPerBulanBantuanPendidikan[$monthIdentity])) {
                        $dataPerBulanBantuanPendidikan[$monthIdentity]++;
                    } else {
                        $dataPerBulanBantuanPendidikan[$monthIdentity] = 1;
                    }
                }
            // dd($dataPerBulanBantuanPendidikan);
            $statusSelesaiBantuanPendidikan = DB::table('indonesia_villages')
                ->select('rekomendasi_bantuan_pendidikans.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
                ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
                ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
                ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
                ->join('rekomendasi_bantuan_pendidikans', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Selesai')
                ->where('indonesia_cities.code', '3273')
                ->where('indonesia_villages.name_village', $name)
                ->count();
            
            // // dd($statusSelesaiBantuanPendidikan);
            // $dataPerMingguBulanSelesaiBantuanPendidikan = array();

            // // data selesaai pper minggu
            // foreach ($statusSelesaiBantuanPendidikan as $statusSelesaiBantuanPendidikans) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);
                
            //     // Ambil informasi minggu dan tahun dari tanggal
            //     $NomerPerMinggu = $createdAt->weekOfMonth;
            //     $yearMonth = $createdAt->format('F Y');
                
            //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
            //     $IndentitasSelsaiPerMinggu = "Minggu ke-" . $NomerPerMinggu;
                
            //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
            //     if (isset($dataPerMingguBulanSelesaiBantuanPendidikan[$IndentitasSelsaiPerMinggu])) {
            //         $dataPerMingguBulanSelesaiBantuanPendidikan[$IndentitasSelsaiPerMinggu]++;
            //     } else {
            //         $dataPerMingguBulanSelesaiBantuanPendidikan[$IndentitasSelsaiPerMinggu] = 1;
            //     }
            // }
            // // dd($dataPerMingguBulanSelesaiBantuanPendidikan);
            // //data selesai per bulan
            // $dataPerBulanSelesaiBantuanPendidika = array();

            // // Loop melalui data yang didapatkan
            // foreach ($statusSelesaiBantuanPendidikan as $statusSelesaiBantuanPendidikans) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);

            //     // Ambil informasi bulan-tahun dari tanggal
            //     $yearMonthBulan = $createdAt->format('F');

            //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
            //     $monthIdentityBulan = $yearMonthBulan;

            //     // Tambahkan atau tambahkan jumlah data per bulan ke array
            //     if (isset($dataPerBulanSelesaiBantuanPendidika[$monthIdentityBulan])) {
            //         $dataPerBulanSelesaiBantuanPendidika[$monthIdentityBulan]++;
            //     } else {
            //         $dataPerBulanSelesaiBantuanPendidika[$monthIdentityBulan] = 1;
            //     }
            // }
            // dd($dataPerBulanSelesaiBantuanPendidika);
            //data proses
            $statusProsesBantuanPendidikan = DB::table('indonesia_villages')
                ->select('rekomendasi_bantuan_pendidikans.created_at', 'indonesia_villages.name_village AS village_name', 'indonesia_districts.name_districts AS district_name')
                ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
                ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
                ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
                ->join('rekomendasi_bantuan_pendidikans', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Teruskan')
                ->where('indonesia_cities.code', '3273')
                ->where('indonesia_villages.name_village', $name)
                ->count();
            // dd($statusProsesBantuanPendidikan);
            // dd($statusProsesBantuanPendidikan);
            // // data selesaai pper minggu
            // $dataPerMingguBulanTeruskanBantuanPendidikan = array();
            // foreach ($statusProsesBantuanPendidikan as $statusProsesBantuanPendidikans) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);
                
            //     // Ambil informasi minggu dan tahun dari tanggal
            //     $NomerPerMinggu = $createdAt->weekOfMonth;
            //     $yearMonth = $createdAt->format('F Y');
                
            //     // Formatkan identitas minggu dan bulan-tahun menjadi string (misal: "Minggu ke-2 Juli 2023")
            //     $IndentitasTeruskanPerMinggu = "Minggu ke-" . $NomerPerMinggu;
                
            //     // Tambahkan atau tambahkan jumlah data per minggu dalam bulan ke array
            //     if (isset($dataPerMingguBulanTeruskanBantuanPendidikan[$IndentitasTeruskanPerMinggu])) {
            //         $dataPerMingguBulanTeruskanBantuanPendidikan[$IndentitasTeruskanPerMinggu]++;
            //     } else {
            //         $dataPerMingguBulanTeruskanBantuanPendidikan[$IndentitasTeruskanPerMinggu] = 1;
            //     }
            // }
            // // dd($dataPerMingguBulanTeruskanBantuanPendidikan);
            // //data Teruskan per bulan
            // $dataPerBulanTeruskanBantuanPendidikan = array();

            // // Loop melalui data yang didapatkan
            // foreach ($statusProses as $statusProsess) {
            //     // Konversi tanggal string menjadi objek Carbon untuk memudahkan manipulasi tanggal
            //     $createdAt = Carbon::parse($kelurahan->created_at);

            //     // Ambil informasi bulan-tahun dari tanggal
            //     $yearMonthBulanTeruskan = $createdAt->format('F');

            //     // Formatkan identitas bulan-tahun menjadi string (misal: "Juli 2023")
            //     $monthIdentityBulanTeruskan = $yearMonthBulanTeruskan;

            //     // Tambahkan atau tambahkan jumlah data per bulan ke array
            //     if (isset($dataPerBulanTeruskanBantuanPendidikan[$monthIdentityBulanTeruskan])) {
            //         $dataPerBulanTeruskanBantuanPendidikan[$monthIdentityBulanTeruskan]++;
            //     } else {
            //         $dataPerBulanTeruskanBantuanPendidikan[$monthIdentityBulanTeruskan] = 1;
            //     }
            // }
        // dd($dataPerHariBerdasarkanPerMingguBantuanPendidikan);
        //sampai sini 

        $dataPerHariBerdasarkanPerMingguBantuanPendidikans = json_encode($dataPerHariBerdasarkanPerMingguBantuanPendidikan);
        $dataPerMingguBulans = json_encode($dataPerMingguBulan);
        $dataPerBulanJson = json_encode($dataPerBulan);
        // $dataPerMingguBulanSelesai = json_encode($dataPerMingguBulanSelesai);
        $dataPerHariBerdasarkanPerMinggu = json_encode($dataPerHariBerdasarkanPerMinggu);
        $dataPerMingguBulanBantuanPendidikan = json_encode($dataPerMingguBulanBantuanPendidikan);
        $dataPerBulanBantuanPendidikans = json_encode($dataPerBulanBantuanPendidikan);
        // $dataPerMingguBulanSelesaiBantuanPendidikan = json_encode($dataPerMingguBulanSelesaiBantuanPendidikan);
        // $dataPerHariBerdasarkanPerMingguBantuanPendidikan = json_encode($dataPerHariBerdasarkanPerMingguBantuanPendidikan);
        // dd($dataPerBulanJson);
        // dd($dataPerBulanBantuanPendidikans);
        return view('grafik.indexiBantuanPendidikan',compact('dataPerMingguBulans',
                                            'dataPerBulanJson',
                                            'lokasiData',
                                            'dataPerHariBerdasarkanPerMinggu',
                                            'statusProses',
                                            'statusSelesai',
                                            'dataPerMingguBulanBantuanPendidikan',
                                            'dataPerBulanBantuanPendidikans',
                                            'statusProsesBantuanPendidikan',
                                            'statusSelesaiBantuanPendidikan',
                                          
                                            'dataPerHariBerdasarkanPerMingguBantuanPendidikans'));
        
    }

    public function DraftBantuanBpGrafik(Request $request, $name)
    {
        $query = DB::table('rekomendasi_bantuan_pendidikans')
        ->leftJoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
        // ->leftJoin('roles', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_BantuanPendidikan')
        ->leftJoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
        ->leftJoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
        ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts', 'users.name')
        ->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Draft')
        ->where('b.name_village', $name);
        // dd($query);
            // ->Where(function ($query) use ($name) {
            // ->where('rekomendasi_bantuan_pendidikans.status_aksi_sudtks', 'Draft')
            // ->where('b.name_village', $name);
            
                // $query->where('rekomendasi_bantuan_pendidikans.createdby_sudtks',  Auth::user()->id);
            // });
        // dd($query);
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_bantuan_pendidikan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function ProsestBantuanBpGrafik(Request $request, $name)
    {
        $query = DB::table('rekomendasi_bantuan_pendidikans')
        ->leftJoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
        // ->leftJoin('roles', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_BantuanPendidikan')
        ->leftJoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
        ->leftJoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
        ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts', 'users.name')
        ->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Teruskan')
        ->where('b.name_village', $name);
        
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_bantuan_pendidikan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function SelesaiBantuanBpGrafik(Request $request, $name)
    {
        $query = DB::table('rekomendasi_bantuan_pendidikans')
        ->leftJoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
        // ->leftJoin('roles', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_BantuanPendidikan')
        ->leftJoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
        ->leftJoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
        ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts', 'users.name')
        ->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Selesai')
        ->where('b.name_village', $name);
        // dd($query);
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_bantuan_pendidikan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
 
}
