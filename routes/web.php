<?php

use App\Http\Controllers\TestApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return phpinfo();
});

Route::get('test', [TestApiController::class, 'test']);