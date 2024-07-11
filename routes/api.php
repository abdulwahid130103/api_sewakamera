<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get_user',[UserController::class,'index']);
Route::post('/login',[UserController::class,'login']);
Route::get('/get_produk',[UserController::class,'get_produk']);
Route::get('/get_produk_terbaru',[UserController::class,'get_produk_terbaru']);
Route::get('/get_rekomendasi_produk',[UserController::class,'get_rekomendasi_produk']);
Route::get('/get_produk_terlaris',[UserController::class,'get_produk_terlaris']);
Route::get('/get_list_produk',[UserController::class,'get_list_produk']);
Route::get('/get_detail_produk/{id}',[UserController::class,'get_detail_produk']);
Route::get('/get_profile/{id}',[UserController::class,'get_profile']);
Route::get('/get_detail_ulasan/{id}',[UserController::class,'get_detail_ulasan']);
Route::get('/get_detail_rating/{id}',[UserController::class,'get_detail_rating']);
Route::get('/get_category',[UserController::class,'get_category']);
Route::get('/get_category_spinner',[UserController::class,'get_category_spinner']);
Route::get('/get_detail_mitra/{id}',[UserController::class,'get_detail_mitra']);

Route::post('/insert_keranjang',[UserController::class,'insert_keranjang']);
Route::get('/get_keranjang/{id}',[UserController::class,'get_keranjang']);
Route::delete('/delete_keranjang/{id}',[UserController::class,'delete_keranjang']);
Route::put('/update_qty_keranjang/{id}',[UserController::class,'update_qty_keranjang']);
Route::get('/get_mitra',[UserController::class,'get_mitra']);


Route::post('/create_transaksi_booking', [UserController::class,'create_transaksi_booking']);
Route::get('/get_booking_transaksi/{id}', [UserController::class,'get_booking_transaksi']);
Route::get('/get_dibayar_transaksi/{id}', [UserController::class,'get_dibayar_transaksi']);
Route::get('/get_cek_expired/{id}', [UserController::class,'get_cek_expired']);
Route::get('/get_lunas_transaksi/{id}', [UserController::class,'get_lunas_transaksi']);
Route::get('/get_dipinjam_transaksi/{id}', [UserController::class,'get_dipinjam_transaksi']);
Route::get('/get_selesai_transaksi/{id}', [UserController::class,'get_selesai_transaksi']);
Route::get('/get_tolak_transaksi/{id}', [UserController::class,'get_tolak_transaksi']);
Route::get('/get_expired_transaksi/{id}', [UserController::class,'get_expired_transaksi']);
Route::get('/get_detail_history', [UserController::class,'get_detail_history']);
Route::get('/get_produk_rating', [UserController::class,'get_produk_rating']);
Route::get('/get_terverifikasi_transaksi/{id}', [UserController::class,'get_terverifikasi_transaksi']);
Route::post('/charge', [UserController::class, 'charge']);
Route::post('/create_data_rating', [UserController::class, 'create_data_rating']);
Route::get('/get_detail_transaksi_bayar', [UserController::class,'get_detail_transaksi_bayar']);
Route::post('/get_update_transaksi_status', [UserController::class,'get_update_transaksi_status']);
Route::post('/get_update_status_expired', [UserController::class,'get_update_status_expired']);
Route::post('/register', [UserController::class,'register']);
Route::post('/update_profile', [UserController::class,'update_profile']);
Route::post('/update_password', [UserController::class,'update_password']);
