<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\api\PegawaiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('users', [UserController::class, 'index']);
Route::get('roles', [RoleController::class, 'index']);
Route::get('pegawai', [PegawaiController::class, 'index']);
Route::get('absen-pegawai', [PegawaiController::class, 'absenpegawai']);


