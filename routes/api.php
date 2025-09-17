<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReportApiController;

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

// Reports API Routes
Route::middleware(['auth:sanctum'])->prefix('reports')->name('api.reports.')->group(function () {
    // Report types and capabilities
    Route::get('/types', [ReportApiController::class, 'reportTypes']);
    
    // Generate report data
    Route::post('/generate', [ReportApiController::class, 'generate']);
    
    // Statistics endpoint
    Route::get('/statistics', [ReportApiController::class, 'statistics']);
    
    // Export management
    Route::post('/export', [ReportApiController::class, 'export']);
    Route::get('/export/{exportId}/status', [ReportApiController::class, 'exportStatus']);
    Route::get('/export/{exportId}/download', [ReportApiController::class, 'download'])->name('download');
    Route::get('/export-history', [ReportApiController::class, 'exportHistory']);
});