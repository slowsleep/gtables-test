<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/records/generate', [App\Http\Controllers\Api\RecordController::class, 'generate']);
Route::delete('/records/destroy-all', [App\Http\Controllers\Api\RecordController::class, 'destroyAll']);

Route::post('/record', [App\Http\Controllers\Api\RecordController::class, 'store']);
Route::get('/record/{id}', [App\Http\Controllers\Api\RecordController::class, 'show']);
Route::put('/record/{id}', [App\Http\Controllers\Api\RecordController::class, 'update']);
Route::delete('/record/{id}', [App\Http\Controllers\Api\RecordController::class, 'destroy']);
