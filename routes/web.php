<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PengaturanWilayahController;
use App\Http\Controllers\ProfileController;

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
