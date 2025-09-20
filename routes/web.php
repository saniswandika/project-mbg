<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GrafikController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LogistikController;
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

    Route::resource('profile', App\Http\Controllers\ProfileController::class);
    Route::post('profilepassword', [ProfileController::class, 'password_action'])->name('password.action');
    Route::post('profilenama', [ProfileController::class, 'name_action'])->name('nama.action');
    Route::post('profileemail', [ProfileController::class, 'email_action'])->name('email.action');
});


Route::prefix('logistik')->name('logistik.')->group(function () {
    // Logistik
    Route::get('/master_barang', [LogistikController::class, 'master_barang'])->name('master_barang');
    Route::get('/tambah_master_barang', [LogistikController::class, 'tambah_master_barang'])->name('tambah_master_barang');
    Route::post('tambah_master_barang', [LogistikController::class, 'proses_tambah_barang_master'])->name('proses_tambah_barang_master');
    // list stok
    Route::get('/list_barang', [LogistikController::class, 'index'])->name('list_barang');
    Route::get('/pengajuan_barang', [LogistikController::class, 'pengajuan_barang'])->name('pengajuan_barang');
    Route::post('/pengajuan_barang', [LogistikController::class, 'proses_pengajuan_barang'])->name('proses_pengajuan_barang');
    // edit
    // Route::get('/', [LogistikController::class, 'index'])->name('index');
    Route::get('/edit/{id}', [LogistikController::class, 'edit_master_barang'])->name('edit_master_barang');
    Route::put('/update/{id}', [LogistikController::class, 'update_master_barang'])->name('update_master_barang'); // Menggunakan PUT untuk update
    //delete
    Route::delete('/destroy/{id}', [LogistikController::class, 'destroy_master_barang'])->name('destroy_master_barang');
});