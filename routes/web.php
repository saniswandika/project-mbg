<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PengaturanWilayahController;
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
use App\Http\Controllers\CheckoutController;

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
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::resource('roles', RoleController::class);
});

// Wilayah
Route::post('/get-kota', [PengaturanWilayahController::class, 'getKota'])->name('getKota');
Route::get('/kecamatan/getByRegency/{regencyId}', [PengaturanWilayahController::class, 'getKecamatanByRegency']);
Route::get('/kelurahan/getByRegency/{kelurahanId}', [PengaturanWilayahController::class, 'getKelurahanByRegency']);
Route::get('/Pengaturan_wilayah', [PengaturanWilayahController::class, 'listwilayah'])->name('Pengaturan_wilayah');
Route::get('/tambah-wilayah', [PengaturanWilayahController::class, 'create'])->name('rubahwilayah');
Route::get('/status/update', [PengaturanWilayahController::class, 'updateStatus'])->name('users.update.status');
Route::post('/add-wilayah', [PengaturanWilayahController::class, 'store'])->name('add_wilayah.store');

// Events
Route::post('/events', [App\Http\Controllers\CalendarController::class, 'index']);

Route::group(['middleware' => ['auth']],function(){
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::resource('roles', RoleController::class);
    Route::resource('users', App\Http\Controllers\UserController::class);

    Route::resource('profile', ProfileController::class);
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
    Route::get('/detail_pengajuan_barang/{id}', [LogistikController::class, 'detail_pengajuan_barang'])->name('detail_pengajuan_barang');
    Route::put('/revisi_pengajuan_barang/{id}', [LogistikController::class, 'revisi_pengajuan_barang'])->name('revisi_pengajuan_barang');
    Route::put('/approve_pengajuan_barang/{id}', [LogistikController::class, 'approve_pengajuan_barang'])->name('approve_pengajuan_barang');
    Route::put('/reject_pengajuan_barang/{id}', [LogistikController::class, 'reject_pengajuan_barang'])->name('reject_pengajuan_barang');
    Route::post('/logistik/verify-approve/{id}', [LogistikController::class, 'verifyApprove'])->name('verify_approve');
    Route::delete('/hapus_pengajuan_barang/{id}/{id_home}', [LogistikController::class, 'hapus_pengajuan_barang'])->name('hapus_pengajuan_barang');
    // edit
    // Route::get('/', [LogistikController::class, 'index'])->name('index');
    Route::get('/edit/{id}', [LogistikController::class, 'edit_master_barang'])->name('edit_master_barang');
    Route::put('/update/{id}', [LogistikController::class, 'update_master_barang'])->name('update_master_barang'); // Menggunakan PUT untuk update
    Route::post('/keranjang/add', [CheckoutController::class, 'addToCart'])->name('add_to_cart');
    Route::get('/keranjang', [CheckoutController::class, 'showCart'])->middleware('auth')->name('ambil_barang');
    Route::delete('/keranjang/{id}', [CheckoutController::class, 'removeFromCart'])->name('hapus_keranjang');
    Route::post('/keranjang/ajukan_pengambilan', [CheckoutController::class, 'ajukan_pengambilan'])->name('ajukan_pengambilan');
    Route::post('/keranjang/approve_keranjang', [CheckoutController::class, 'approve_keranjang'])->name('approve_keranjang');
    Route::post('/keranjang/reject_keranjang', [CheckoutController::class, 'reject_keranjang'])->name('reject_keranjang');
    Route::post('/keranjang/terima_keranjang', [CheckoutController::class, 'terima_keranjang'])->name('terima_keranjang');
    Route::post('/keranjang/status_change', [CheckoutController::class, 'status_change'])->name('status_change');
    Route::get('/history_keranjang', [CheckoutController::class, 'history_keranjang'])->middleware('auth')->name('history_keranjang');
    //delete
    Route::delete('/destroy/{id}', [LogistikController::class, 'destroy_master_barang'])->name('destroy_master_barang');
});