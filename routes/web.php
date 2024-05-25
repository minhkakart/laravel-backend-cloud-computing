<?php

use App\Http\Controllers\api\CloudTranslateController;
use Illuminate\Support\Facades\Route;

Route::resource('translate', CloudTranslateController::class);

Route::get('/', function () {
    return view('welcome');
});
