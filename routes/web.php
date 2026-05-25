<?php

use App\Http\Controllers\CTKDocumentController;
use App\Http\Controllers\EmployeeDokumenPTController;
use App\Http\Controllers\EmployeeSertifikatController;
use App\Http\Controllers\PrivateDocumentController;
use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/terms-of-service', 'terms-of-service')->name('terms-of-service');

// Employee Sertifikat download route
Route::get('/karyawan-lpk/{employee}/sertifikat/download', [EmployeeSertifikatController::class, 'download'])
    ->name('karyawan-lpk.sertifikat.download')
    ->middleware('auth');

// CTK Document download route
Route::get('/ctk/documents/{document}/download', [CTKDocumentController::class, 'download'])
    ->name('ctk.documents.download')
    ->middleware('auth');

// Employee PT dokumen kepegawaian download route
Route::get('/karyawan-pt/{employee}/dokumen/download', [EmployeeDokumenPTController::class, 'download'])
    ->name('karyawan-pt.dokumen.download')
    ->middleware('auth');

// Private document download routes
Route::middleware('auth')->group(function () {
    Route::get('/ctk/medical/{medical}/download', [PrivateDocumentController::class, 'downloadMedical'])
        ->name('ctk.medical.download');
    Route::get('/ctk/visa/{visa}/download', [PrivateDocumentController::class, 'downloadVisa'])
        ->name('ctk.visa.download');
    Route::get('/ctk/{ctk}/opp/download', [PrivateDocumentController::class, 'downloadOpp'])
        ->name('ctk.opp.download');
});
