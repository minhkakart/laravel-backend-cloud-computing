<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return phpinfo();
});

Route::get('upload', [UploadController::class, 'index']);
Route::post('upload_object', [UploadController::class, 'upload_object'])->name('upload');