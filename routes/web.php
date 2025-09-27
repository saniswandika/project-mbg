<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GrafikController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LogistikController;
use App\Http\Controllers\BahanOlahanController;
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
Route::group(['middleware' => ['prevent-back-history', 'auth', 'TimeOutLogin']], function () {

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
Route::group(['middleware' => ['auth']], function () {
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

Route::prefix('bahan_olahan')->name('bahan_olahan.')->group(function () {
    // Logistik
    Route::get('/bahan_olahan', [BahanOlahanController::class, 'bahan_olahan'])->name('bahan_olahan');
    Route::get('/tambah_bahan_olahan', [BahanOlahanController::class, 'tambah_bahan_olahan'])->name('tambah_bahan_olahan');
    Route::post('tambah_bahan_olahan', [BahanOlahanController::class, 'proses_tambah_bahan_master'])->name('proses_tambah_bahan_master');
    Route::get('/edit/{id}', [BahanOlahanController::class, 'edit_bahan_olahan'])->name('edit_master_bahan');
    Route::put('/update/{id}', [BahanOlahanController::class, 'update_bahan_olahan'])->name('update_bahan_olahan'); // Menggunakan PUT untuk update
    Route::delete('/destroy/{id}', [BahanOlahanController::class, 'destroy_master_bahan'])->name('destroy_master_bahan');
    // list stok
    Route::get('/list_bahan', [BahanOlahanController::class, 'index'])->name('list_bahan');
    Route::get('/pengajuan_bahan', [BahanOlahanController::class, 'pengajuan_bahan'])->name('pengajuan_bahan');
    Route::post('/pengajuan_bahan', [BahanOlahanController::class, 'proses_pengajuan_bahan'])->name('proses_pengajuan_bahan');
    Route::get('/detail_pengajuan_bahan/{id}', [BahanOlahanController::class, 'detail_pengajuan_bahan'])->name('detail_pengajuan_bahan');
    Route::put('/revisi_pengajuan_bahan/{id}', [BahanOlahanController::class, 'revisi_pengajuan_bahan'])->name('revisi_pengajuan_bahan');
    Route::put('/approve_pengajuan_bahan/{id}', [BahanOlahanController::class, 'approve_pengajuan_bahan'])->name('approve_pengajuan_bahan');
    Route::put('/reject_pengajuan_bahan/{id}', [BahanOlahanController::class, 'reject_pengajuan_bahan'])->name('reject_pengajuan_bahan');
    Route::post('/bahan_olahan/verify-approve/{id}', [BahanOlahanController::class, 'verifyApprove'])->name('verify_approve');
    Route::delete('/hapus_pengajuan_bahan/{id}/{id_home}', [BahanOlahanController::class, 'hapus_pengajuan_bahan'])->name('hapus_pengajuan_bahan');
    // edit
    // Route::get('/', [BahanOlahanController::class, 'index'])->name('index');
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
});
