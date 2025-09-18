<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GrafikController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PengaduanController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PengaturanWilayahController;
use App\Http\Controllers\pdfController;
use App\Http\Controllers\pdfrekativasiController;
use App\Http\Controllers\pdfyayasanController;
use App\Http\Controllers\rekomendasi_admin_kependudukanController;
use App\Http\Controllers\rekomendasi_bantuan_pendidikanController;
use App\Http\Controllers\rekomendasi_biaya_perawatanController;
use App\Http\Controllers\rekomendasi_daftar_ulang_yayasanController;
use App\Http\Controllers\rekomendasi_keringanan_pbbController;
use App\Http\Controllers\rekomendasi_pelaporan_pubController;
use App\Http\Controllers\rekomendasi_pengangkatan_anakController;
use App\Http\Controllers\rekomendasi_rehabilitasi_sosialController;
use App\Http\Controllers\rekomendasi_rekativasi_pbi_jkController;
use App\Http\Controllers\rekomendasi_terdaftar_dtksController;
use App\Models\Pengaduan;
use App\Models\rekomendasi_terdaftar_yayasan;
use Dompdf\Adapter\PDFLib;
use Symfony\Component\HttpKernel\Profiler\Profile;
use App\Http\Controllers\rekomendasi_terdaftar_yayasanController;
use App\Http\Controllers\rekomendasi_yayasan_provinsiController;
use App\Models\rekomendasi_pengumpulan_undian_berhadiah;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\Role;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('auth.login');
});
Route::get('pengaduans', function () {
   return view('pengaduans.index');
});
Route::get('pengaduans/dashboard', function () {
    return view('pengaduans.dashboard');
});

Route::get('/grafik/{name}', [GrafikController::class, 'GrafikDtks'])->name('Grafik.Index');
Route::get('/draft-bantuan-sudtks-grafik/{name}', [GrafikController::class, 'DraftBantuanSudtksGrafik'])->name('DraftBantuanSudtksGrafik.get');
Route::get('/proses-bantuan-sudtks-grafik/{name}', [GrafikController::class, 'ProsestBantuanSudtksGrafik'])->name('ProsestBantuanSudtksGrafik.get');
Route::get('/selesai-bantuan-sudtks-grafik/{name}', [GrafikController::class, 'SelesaiBantuanSudtksGrafik'])->name('SelesaiBantuanSudtksGrafik.get');

Route::get('/grafikBp/{name}', [GrafikController::class, 'GrafikBantuanPendidikan'])->name('GrafikBantuanPendidikan.Index');
Route::get('/draft-bantuan-Bp-grafik/{name}', [GrafikController::class, 'DraftBantuanBpGrafik'])->name('DraftBantuanBpGrafik.get');
Route::get('/proses-bantuan-Bp-grafik/{name}', [GrafikController::class, 'ProsestBantuanBpGrafik'])->name('ProsestBantuanBpGrafik.get');
Route::get('/selesai-bantuan-Bp-grafik/{name}', [GrafikController::class, 'SelesaiBantuanBpGrafik'])->name('SelesaiBantuanBpGrafik.get');


Route::get('/maps', function () {
    return view('command');
});


Route::get('/maps-bantuan-pendidikan', function () {
    return view('grafik.bantuan_pendidikan');
});

Route::get('/DaftarAkun', [App\Http\Controllers\RegisterController::class, 'create'])->name('DaftarAkun');
Route::post('/register-store', [App\Http\Controllers\RegisterController::class, 'store'])->name('register-store');


Auth::routes();
Route::group(['middleware' => ['prevent-back-history','auth','TimeOutLogin']],function(){
    
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::resource('roles', RoleController::class);
});

//wilayah
Route::post('/get-kota', [PengaturanWilayahController::class, 'getKota'])->name('getKota');
Route::get('/kecamatan/getByRegency/{regencyId}', [PengaturanWilayahController::class, 'getKecamatanByRegency']);
Route::get('/kelurahan/getByRegency/{kelurahanId}', [PengaturanWilayahController::class, 'getKelurahanByRegency']);
Route::get('/Pengaturan_wilayah', [PengaturanWilayahController::class, 'listwilayah'])->name('Pengaturan_wilayah');
Route::get('/tambah-wilayah', [PengaturanWilayahController::class, 'create'])->name('rubahwilayah');
Route::get('/status/update', [PengaturanWilayahController::class, 'updateStatus'])->name('users.update.status');
Route::post('/add-wilayah', [PengaturanWilayahController::class, 'store'])->name('add_wilayah.store');
//tutup wilayah
// Route::post('/calendar', [App\Http\Controllers\CalendarController::class, 'index']);
Route::post('/events', [App\Http\Controllers\CalendarController::class, 'index']);
Route::group(['middleware' => ['auth']],function(){
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::resource('roles', RoleController::class);
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('jadwals', App\Http\Controllers\jadwalController::class);
    Route::resource('rekom-dtks', App\Http\Controllers\rekomDtksController::class);
    Route::resource('suket-dtks', App\Http\Controllers\suketDtksController::class);
    Route::resource('pengaduans', App\Http\Controllers\PengaduanController::class);
    Route::resource('rekomendasi_pengangkatan_anaks', App\Http\Controllers\rekomendasi_pengangkatan_anakController::class);
    // Route::resource('pengaduans', App\Http\Controllers\PengaduanController::class);
    Route::resource('rekomendasi_terdaftar_yayasans', App\Http\Controllers\rekomendasi_terdaftar_yayasanController::class);
    Route::resource('rekomendasi_pub', App\Http\Controllers\rekomendasi_pengumpulan_undian_berhadiahController::class);
    Route::resource('rekomendasi_bantuan_pendidikans', App\Http\Controllers\rekomendasi_bantuan_pendidikanController::class);
    Route::resource('rekomendasi_rekativasi_pbi_jks', App\Http\Controllers\rekomendasi_rekativasi_pbi_jkController::class);
    Route::resource('rekomendasi_admin_kependudukans', App\Http\Controllers\rekomendasi_admin_kependudukanController::class);
    Route::resource('rekomendasi_rehabilitasi_sosials', App\Http\Controllers\rekomendasi_rehabilitasi_sosialController::class);
    Route::resource('rekomendasi_terdaftar_dtks', App\Http\Controllers\rekomendasi_terdaftar_dtksController::class);
    Route::resource('rekomendasi_biaya_perawatans', App\Http\Controllers\rekomendasi_biaya_perawatanController::class);
    Route::resource('rekomendasi_daftar_ulang_yayasans', App\Http\Controllers\rekomendasi_daftar_ulang_yayasanController::class);
    Route::resource('rekomendasi_yayasan_provinsis', App\Http\Controllers\rekomendasi_yayasan_provinsiController::class);
    Route::resource('rekomendasi_pelaporan_pubs', App\Http\Controllers\rekomendasi_pelaporan_pubController::class);
    Route::resource('profile', App\Http\Controllers\ProfileController::class);
    Route::post('profilepassword', [ProfileController::class, 'password_action'])->name('password.action');
    Route::post('profilenama', [ProfileController::class, 'name_action'])->name('nama.action');
    Route::post('profileemail', [ProfileController::class, 'email_action'])->name('email.action');
});

Route::get('/petugas/{id}', [rekomendasi_terdaftar_yayasanController::class, 'getPetugas'])->name('getPetugas');
Route::get('/getPetugasAdminduk/{id}', [rekomendasi_admin_kependudukanController::class, 'getPetugasAdminduk'])->name('getPetugasAdminduk');
Route::get('/getPetugasBiayaPerawatan/{id}', [rekomendasi_biaya_perawatanController::class, 'getPetugasBiayaPerawatan'])->name('getPetugasBiayaPerawatan');
Route::get('/getPetugaRehabilitasiSosial/{id}', [rekomendasi_rehabilitasi_sosialController::class, 'getPetugaRehabilitasiSosial'])->name('getPetugaRehabilitasiSosial');
Route::get('/getPetugasPbb/{id}', [rekomendasi_keringanan_pbbController::class, 'getPetugasPbb'])->name('getPetugasPbb');
Route::get('/getPetugaPbiJk/{id}', [rekomendasi_rekativasi_pbi_jkController::class, 'getPetugaPbiJk'])->name('getPetugaPbiJk');
Route::get('/getPetugasBantuanPendidikan/{id}', [rekomendasi_bantuan_pendidikanController::class, 'getPetugasBantuanPendidikan'])->name('getPetugasBantuanPendidikan');



// Route::resource('/download', App\Http\Controllers\rekomendasi_rehabilitasi_sosialController::class);
// //mengambil data file imagekit

//mengambil data file imagekit
Route::get('file_ktp_terlapor_bantuan_pendidikans/{rekomendasi_bantuan_pendidikan}', [rekomendasi_bantuan_pendidikanController::class, 'file_ktp_terlapor_bantuan_pendidikans'])->name('get_data_file_ktp_terlapor_bantuan_pendidikans');
Route::get('file_keterangan_dtks_bantuan_pendidikans/{rekomendasi_bantuan_pendidikan}', [rekomendasi_bantuan_pendidikanController::class, 'file_keterangan_dtks_bantuan_pendidikans'])->name('get_data_file_keterangan_dtks_bantuan_pendidikans');
Route::get('file_kk_terlapor_bantuan_pendidikans/{rekomendasi_bantuan_pendidikan}', [rekomendasi_bantuan_pendidikanController::class, 'file_kk_terlapor_bantuan_pendidikans'])->name('get-data-imagekit');
Route::get('file_pendukung_bantuan_pendidikans/{rekomendasi_bantuan_pendidikan}', [rekomendasi_bantuan_pendidikanController::class, 'file_pendukung_bantuan_pendidikans'])->name('get_file_pendukung_bantuan_pendidikans');
// mengambil data file imagekit 

// test
Route::get('getCountKelurahan/{name_kelurahan}', [rekomendasi_bantuan_pendidikanController::class, 'getCountKelurahan'])->name('contoh');
Route::get('getCountKelurahandtks/{name_kelurahan}', [rekomendasi_terdaftar_dtksController::class, 'getCountKelurahan'])->name('contohdtks');


//Pengaduan AJAX API
Route::get('getdata', [PengaduanController::class, 'draft'])->name('getdata');
Route::get('diproses', [PengaduanController::class, 'diproses'])->name('diproses');
Route::get('teruskan', [PengaduanController::class, 'teruskan'])->name('teruskan');
Route::get('/selesai', [PengaduanController::class, 'selesai'])->name('selesai');

//DTKS and Prelist API
Route::get('/prelistDTKS', [PengaduanController::class, 'prelistDTKS'])->name('prelist_DTKS');
Route::get('/prelistPage', [PengaduanController::class, 'prelistPage'])->name('prelistPage');

Route::get('/detaillogpengaduan/{detailpengaduan}', [PengaduanController::class, 'log_detail_pengaduan'])->name('detaillogpengaduan');
Route::get('/detailpengaduan/{id}', [PengaduanController::class, 'detail_pengaduan'])->name('detailpengaduan');
Route::get('/draft-rekomendasi-terdaftar-yayasan-pagination', [rekomendasi_terdaftar_yayasanController::class, 'pagination'])->name('pagination');

//Yayasan AJAX API
Route::get('/draft-rekomendasi-terdaftar-yayasan', [rekomendasi_terdaftar_yayasanController::class, 'getDataDraft'])->name('draft-rekomendasi-terdaftar-yayasan');
Route::get('/diproses-rekomendasi-terdaftar-yayasan', [rekomendasi_terdaftar_yayasanController::class, 'getDataDiProses'])->name('diproses-rekomendasi-terdaftar-yayasan');
Route::get('/teruskan-rekomendasi-terdaftar-yayasan', [rekomendasi_terdaftar_yayasanController::class, 'getDataTeruskan'])->name('teruskan-rekomendasi-terdaftar-yayasan');
Route::get('/selesai-rekomendasi-terdaftar-yayasan', [rekomendasi_terdaftar_yayasanController::class, 'getDataSelesai'])->name('selesai-rekomendasi-terdaftar-yayasan');
Route::get('/log-bantuan-pendidikan/{id}', [rekomendasi_terdaftar_yayasanController::class, 'detail_log_bantuan_pendidikan'])->name('detail_log_bantuan_pendidikan');
Route::get('/file_permohonan_yayasan/{id}', [rekomendasi_terdaftar_yayasanController::class, 'FileRekomYayasan'])->name('FileRekomYayasan');

//Daftar Ulang Yayasan AJAX API
Route::get('/List-Terdaftar-Yayasan', [rekomendasi_daftar_ulang_yayasanController::class, 'listTerdfatarYayasan'])->name('listTerdatarYayasan');
Route::get('/diproses-rekomendasi-daftar-ulang-yayasan', [rekomendasi_daftar_ulang_yayasanController::class, 'diproses'])->name('diproses-rekomendasi-daftar-ulang-yayasan');
Route::get('/teruskan-rekomendasi-daftar-ulang-yayasan', [rekomendasi_daftar_ulang_yayasanController::class, 'teruskan'])->name('teruskan-rekomendasi-daftar-ulang-yayasan');
Route::get('/selesai-rekomendasi-daftar-ulang-yayasan', [rekomendasi_daftar_ulang_yayasanController::class, 'selesai'])->name('selesai-rekomendasi-daftar-ulang-yayasan');
Route::get('/file_permohonan_Daftar_Ulang_Yayasan/{id}', [rekomendasi_daftar_ulang_yayasanController::class, 'FileRekomDaftarUlangYayasan'])->name('FileRekomDaftarUlangYayasan');
Route::post('/sertifikat_akreditasi/{id}', [rekomendasi_daftar_ulang_yayasanController::class, 'sertifikat_akreditasi'])->name('sertifikat_akreditasi.upload');

//Yayasan Provinsi AJAX API
Route::get('/listyayasan-rekomendasi-yayasan_provinsi', [rekomendasi_yayasan_provinsiController::class, 'listyayasan'])->name('listyayasan-rekomendasi-yayasan_provinsi');
Route::get('/diproses-rekomendasi-yayasan_provinsi', [rekomendasi_yayasan_provinsiController::class, 'diproses'])->name('diproses-rekomendasi-yayasan_provinsi');
Route::get('/teruskan-rekomendasi-yayasan_provinsi', [rekomendasi_yayasan_provinsiController::class, 'teruskan'])->name('teruskan-rekomendasi-yayasan_provinsi');
Route::get('/selesai-rekomendasi-yayasan_provinsi', [rekomendasi_yayasan_provinsiController::class, 'selesai'])->name('selesai-rekomendasi-yayasan_provinsi');
Route::get('/proses_surat_yayasan_provinsi/{id}', [rekomendasi_yayasan_provinsiController::class, 'prosesSurat'])->name('prosesSurat.edit');
Route::match(['PUT', 'PATCH'], 'proses_edit_data/{id}', [rekomendasi_yayasan_provinsiController::class, 'proses'])->name('proses.edit');

Route::get('/file_permohonan_Yayasan_provinsi/{id}', [rekomendasi_yayasan_provinsiController::class, 'FileRekomYayasanProvinsi'])->name('FileRekomYayasanProvinsi');

//Rekativasi PBI JK AJAX API
Route::get('/draft-rekomendasi-rekativasi', [rekomendasi_rekativasi_pbi_jkController::class, 'draft'])->name('draft-rekomendasi-rekativasi');
Route::get('/diproses-rekomendasi-rekativasi', [rekomendasi_rekativasi_pbi_jkController::class, 'diproses'])->name('diproses-rekomendasi-rekativasi');
Route::get('/teruskan-rekomendasi-rekativasi', [rekomendasi_rekativasi_pbi_jkController::class, 'teruskan'])->name('teruskan-rekomendasi-rekativasi');
Route::get('/selesai-rekomendasi-rekativasi', [rekomendasi_rekativasi_pbi_jkController::class, 'selesai'])->name('selesai-rekomendasi-rekativasi');
Route::get('/cek-id/{Nik}', [rekomendasi_rekativasi_pbi_jkController::class, 'cekIDPBI'])->name('cek-id');
Route::get('/file_file_reaktivasi/{id}', [rekomendasi_rekativasi_pbi_jkController::class, 'FileReaktivasipbijkn'])->name('file_reaktivasi');

//Rehabilitasi Sosial AJAX API
Route::get('/draft-rekomendasi-rehabilitasi', [rekomendasi_rehabilitasi_sosialController::class, 'draft'])->name('draft-rekomendasi-rehabilitasi');
Route::get('/file_surat_rehabsos/{id}', [rekomendasi_rehabilitasi_sosialController::class, 'FileRehabsos'])->name('file_surat_rehabsos');
Route::get('/diproses-rekomendasi-rehabilitasi', [rekomendasi_rehabilitasi_sosialController::class, 'diproses'])->name('diproses-rekomendasi-rehabilitasi');
Route::get('/teruskan-rekomendasi-rehabilitasi', [rekomendasi_rehabilitasi_sosialController::class, 'teruskan'])->name('teruskan-rekomendasi-rehabilitasi');
Route::get('/selesai-rekomendasi-rehabilitasi', [rekomendasi_rehabilitasi_sosialController::class, 'selesai'])->name('selesai-rekomendasi-rehabilitasi');
Route::get('/cek-id/{Nik}', [rekomendasi_rehabilitasi_sosialController::class, 'cekIDRehab'])->name('cek-id');

//Biaya Perawatan AJAX API
Route::get('/draft-rekomendasi-biaya', [rekomendasi_biaya_perawatanController::class, 'draft'])->name('draft-rekomendasi-biaya');
Route::get('/diproses-rekomendasi-biaya', [rekomendasi_biaya_perawatanController::class, 'diproses'])->name('diproses-rekomendasi-biaya');
Route::get('/teruskan-rekomendasi-biaya', [rekomendasi_biaya_perawatanController::class, 'teruskan'])->name('teruskan-rekomendasi-biaya');
Route::get('/selesai-rekomendasi-biaya', [rekomendasi_biaya_perawatanController::class, 'selesai'])->name('selesai-rekomendasi-biaya');
Route::get('/cek-id/{Nik}', [rekomendasi_biaya_perawatanController::class, 'cekIDBiper'])->name('cek-id');
Route::get('/file_biaya_perawatan/{id}', [rekomendasi_biaya_perawatanController::class, 'FileBiayaPerawatan'])->name('file_biaya_perawatan');

//Pengangkatan Anak AJAX API
Route::get('/draft-rekomendasi-pengan', [rekomendasi_pengangkatan_anakController::class, 'draft'])->name('draft-rekomendasi-pengan');
Route::get('/file_permohonan_pengangkatan_anak/{id}', [rekomendasi_pengangkatan_anakController::class, 'FileRekomPengangkatanAnak'])->name('FileRekomPengangkatanAnak');
Route::get('/diproses-rekomendasi-pengan', [rekomendasi_pengangkatan_anakController::class, 'diproses'])->name('diproses-rekomendasi-pengan');
Route::get('/teruskan-rekomendasi-pengan', [rekomendasi_pengangkatan_anakController::class, 'teruskan'])->name('teruskan-rekomendasi-pengan');
Route::get('/selesai-rekomendasi-pengan', [rekomendasi_pengangkatan_anakController::class, 'selesai'])->name('selesai-rekomendasi-pengan');
Route::get('/cek-id/{Nik}', [rekomendasi_pengangkatan_anakController::class, 'cekIDPengan'])->name('cek-id');

//Admin Kependudukan AJAX API
Route::get('/draft-rekomendasi-minkep', [rekomendasi_admin_kependudukanController::class, 'draft'])->name('draft-rekomendasi-minkep');
Route::get('/diproses-rekomendasi-minkep', [rekomendasi_admin_kependudukanController::class, 'diproses'])->name('diproses-rekomendasi-minkep');
Route::get('/teruskan-rekomendasi-minkep', [rekomendasi_admin_kependudukanController::class, 'teruskan'])->name('teruskan-rekomendasi-minkep');
Route::get('/selesai-rekomendasi-minkep', [rekomendasi_admin_kependudukanController::class, 'selesai'])->name('selesai-rekomendasi-minkep');
Route::get('/cek-id/{Nik}', [rekomendasi_admin_kependudukanController::class, 'cekIDMinkep'])->name('cek-id');
Route::get('/file_surat_adminduk/{id}', [rekomendasi_admin_kependudukanController::class, 'FileAdminduk'])->name('file_surat_adminduk');

//Keringanan PBB AJAX API
Route::resource('rekomendasi_keringanan_pbbs', rekomendasi_keringanan_pbbController::class);
Route::get('/rekomendasi_keringanan_pbbs/{id}', [rekomendasi_keringanan_pbbController::class, 'show'])->name('rekomendasi_keringanan_pbbs.detail');
Route::get('/draft-rekomendasi-pbb', [rekomendasi_keringanan_pbbController::class, 'draft'])->name('draft-rekomendasi-pbb');
Route::get('/diproses-rekomendasi-pbb', [rekomendasi_keringanan_pbbController::class, 'diproses'])->name('diproses-rekomendasi-pbb');
Route::get('/teruskan-rekomendasi-pbb', [rekomendasi_keringanan_pbbController::class, 'teruskan'])->name('teruskan-rekomendasi-pbb');
Route::get('/selesai-rekomendasi-pbb', [rekomendasi_keringanan_pbbController::class, 'selesai'])->name('selesai-rekomendasi-pbb');
Route::get('/cek-id/{Nik}', [rekomendasi_keringanan_pbbController::class, 'cekIDPBB'])->name('cek-id');
Route::get('/file_surat_Pbb/{id}', [rekomendasi_keringanan_pbbController::class, 'FilePbb'])->name('file_surat_Pbb');

//Pengumpulan Undian Berhadiah AJAX API
Route::get('/draft-rekomendasi-pub', [rekomendasi_pengumpulan_undian_berhadiah::class, 'draft'])->name('draft-rekomendasi-pub');
Route::get('/diproses-rekomendasi-pub', [rekomendasi_pengumpulan_undian_berhadiah::class, 'diproses'])->name('diproses-rekomendasi-pub');
Route::get('/teruskan-rekomendasi-pub', [rekomendasi_pengumpulan_undian_berhadiah::class, 'teruskan'])->name('teruskan-rekomendasi-pub');
Route::get('/selesai-rekomendasi-pub', [rekomendasi_pengumpulan_undian_berhadiah::class, 'selesai'])->name('selesai-rekomendasi-pub');
Route::get('/cek-id/{Nik}', [rekomendasi_pengumpulan_undian_berhadiah::class, 'cekIDPUB'])->name('cek-id');
    
//Bantuan Pendidikan AJAX API

Route::get('/draft-bantuan-pendidikans', [rekomendasi_bantuan_pendidikanController::class, 'draft'])->name('draft-rekomendasi-bantuan-pendidikans');
Route::get('/diproses-bantuan-pendidikans', [rekomendasi_bantuan_pendidikanController::class, 'diproses'])->name('diproses-bantuan-pendidikans');
Route::get('/teruskan-bantuan-pendidikans', [rekomendasi_bantuan_pendidikanController::class, 'teruskan'])->name('teruskan-bantuan-pendidikans');
Route::get('/selesai-bantuan-pendidikans', [rekomendasi_bantuan_pendidikanController::class, 'selesai'])->name('selesai-bantuan-pendidikans');
Route::get('/detaillogpengaduan/{detailpengaduan}', [PengaduanController::class, 'log_detail_pengaduan'])->name('detaillogpengaduan');
Route::get('/file_surat_pendidikan/{id}', [rekomendasi_bantuan_pendidikanController::class, 'fileSuratPendidikan'])->name('file_surat_pendidikan');

//Surat DTKS AJAX API
Route::get('/draft-bantuan-sudtks', [rekomendasi_terdaftar_dtksController::class, 'draft'])->name('draft-rekomendasi-bantuan-sudtks');
Route::get('/diproses-bantuan-sudtks', [rekomendasi_terdaftar_dtksController::class, 'diproses'])->name('diproses-bantuan-sudtks');
Route::get('/teruskan-bantuan-sudtks', [rekomendasi_terdaftar_dtksController::class, 'teruskan'])->name('teruskan-bantuan-sudtks');
Route::get('/selesai-bantuan-sudtks', [rekomendasi_terdaftar_dtksController::class, 'selesai'])->name('selesai-bantuan-sudtks');
Route::get('/cek-id/{Nik}', [rekomendasi_terdaftar_dtksController::class, 'cekIdDTKS'])->name('cek-id');
Route::get('/file_terdaftar_dtks/{id}', [rekomendasi_terdaftar_dtksController::class, 'FilePbb'])->name('file_terdaftar_dtks');
//Pelaporan PUB AJAX API

Route::get('/draft-pelaporan-pubs', [rekomendasi_pelaporan_pubController::class, 'draft'])->name('draft-rekomendasi-pelaporan-pubs');
Route::get('/diproses-pelaporan-pubs', [rekomendasi_pelaporan_pubController::class, 'diproses'])->name('diproses-pelaporan-pubs');
Route::get('/teruskan-pelaporan-pubs', [rekomendasi_pelaporan_pubController::class, 'teruskan'])->name('teruskan-pelaporan-pubs');
Route::get('/selesai-pelaporan-pubs', [rekomendasi_pelaporan_pubController::class, 'selesai'])->name('selesai-pelaporan-pubs');
Route::get('/pengaduans/search', [PengaduanController::class, 'search'])->name('pengaduans.search');
Route::get('/pengaduans/{pengaduan}/delete', [PengaduanController::class, 'destroy'])->name('pengaduans.delet2');
Route::get('/file_pelaporan_pub/{id}', [rekomendasi_pelaporan_pubController::class, 'FileRekomPelaporanPub'])->name('FileRekomPelaporanPub');


//dokumen pdf rekomendasi
Route::get('/pdfpengaduan/{id}', [pdfController::class, 'show']);
Route::get('/pdfyayasan/{id}', [pdfyayasanController::class, 'show']);
Route::get('/pdfrekativasi/{id}', [pdfrekativasiController::class, 'show']);


// Route::get('/pengaduans/destroy', [PengaduanController::class, 'destroy'])->name('pengaduans.destroy');
Route::get('/cek-id/{Nik}', function($Nik) {
    $found = false;
    $table2 = DB::table('dtks')->where('Nik', $Nik)->first(); 
    if ($table2) {
        $found = true;
        $Id_DTKS = $table2->Id_DTKS; // Ambil data nama jika ID ditemukan
    }else{
        $found = false;
        $Id_DTKS = 'not found data';
    }
    return response()->json([
        'found' => $found,
        'Id_DTKS' => $Id_DTKS
    ]);
});

