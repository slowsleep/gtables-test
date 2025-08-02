<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RecordController;
use App\Http\Controllers\Api\GoogleSheetController;

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

Route::post('/records/generate', [RecordController::class, 'generate']);
Route::delete('/records/destroy-all', [RecordController::class, 'destroyAll']);

Route::post('/record', [RecordController::class, 'store']);
Route::get('/record/{id}', [RecordController::class, 'show']);
Route::put('/record/{id}', [RecordController::class, 'update']);
Route::delete('/record/{id}', [RecordController::class, 'destroy']);

Route::post('/google-sheet-sync', [GoogleSheetController::class, 'storeAndSync']);
Route::delete('/google-sheet-sync', [GoogleSheetController::class, 'destroy']);
