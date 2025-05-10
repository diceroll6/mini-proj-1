<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [UploadController::class, 'index'])->name('home');
Route::post('/upload', [UploadController::class, 'upload'])->name('upload');
Route::get('/upload-row-info/{id}', [UploadController::class, 'upload_row_info'])->name('upload-row-info');