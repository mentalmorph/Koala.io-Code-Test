<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrandController;

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

Route::get('/', fn() => ['status' => 'OK']);
Route::get('/brands', [BrandController::class, 'brands']);
Route::get('/{brandCode}', [BrandController::class, 'brand']);
Route::get('/{brandCode}/locations', [BrandController::class, 'locations']);
Route::get('/{brandCode}/locations/{id}', [BrandController::class, 'location']);
Route::get('/{brandCode}/locations/{id}/menu', [BrandController::class, 'menu']);
