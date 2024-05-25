<?php

use App\Http\Controllers\api\CloudTranslateController;
use Illuminate\Support\Facades\Route;

Route::get('/translate', [CloudTranslateController::class, 'index']);
Route::get('/create', [CloudTranslateController::class, 'create']);

Route::get('/', function () {
    return view('welcome');
});
