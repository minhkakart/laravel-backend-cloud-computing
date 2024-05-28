<?php

use App\Http\Controllers\api\CloudVideoIntelligenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\CloudTranslateController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('cloud-translate')->group(function () {
    Route::get('list-language', [CloudTranslateController::class, 'listLanguage']);
    Route::post('translate', [CloudTranslateController::class, 'translate']);
    Route::post('detect-language', [CloudTranslateController::class, 'detectLanguage']);
    Route::post('detect-labels', [CloudVideoIntelligenceController::class, 'labelDetection']);
});