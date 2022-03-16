<?php

use Illuminate\Support\Facades\Route;

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

Route::post('hash_check', 'App\Http\Controllers\upload@hashCheck');
Route::post('chunks_upload', 'App\Http\Controllers\upload@chunksUpload');
Route::post('chunks_merge', 'App\Http\Controllers\upload@chunks_merge');
