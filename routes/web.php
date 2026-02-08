<?php

use App\Http\Controllers\CTKDocumentController;
use App\Http\Controllers\EmployeeSertifikatController;
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

// Employee Sertifikat download route
Route::get('/karyawan-lpk/{employee}/sertifikat/download', [EmployeeSertifikatController::class, 'download'])
    ->name('karyawan-lpk.sertifikat.download')
    ->middleware('auth');

// CTK Document download route
Route::get('/ctk/documents/{document}/download', [CTKDocumentController::class, 'download'])
    ->name('ctk.documents.download')
    ->middleware('auth');
