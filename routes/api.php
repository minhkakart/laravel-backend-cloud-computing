<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CloudVideoIntelligenceController;
use App\Http\Controllers\api\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\CloudTranslateController;


// Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();

//     return response()->json([
//         'message' => 'Email verified'
//     ]);
// })->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

// Route::post('email/verification-notification', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();

//     return response()->json([
//         'message' => 'Email verification link sent'
//     ]);
// })->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
Route::get('csrf-cookie', function () {
    return response()->json([
        'message' => 'CSRF cookie set successfully'
    ]);
});
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
Route::any('unauthenticated', function () {
    return response()->json([
        'message' => 'Unauthenticated'
    ], 401);
})->name('unauthenticated');

Route::get('user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('cloud-translate')->group(function () {
    Route::get('list-language', [CloudTranslateController::class, 'listLanguage'])->middleware('auth:sanctum');
    Route::post('translate', [CloudTranslateController::class, 'translate'])->middleware('auth:sanctum');
    Route::post('detect-language', [CloudTranslateController::class, 'detectLanguage'])->middleware('auth:sanctum');
});
Route::prefix('video-intelligence')->group(function () {
    Route::post('detect-labels', [CloudVideoIntelligenceController::class, 'labelDetection'])->middleware('auth:sanctum');
    Route::post('detect-face', [CloudVideoIntelligenceController::class, 'faceDetection'])->middleware('auth:sanctum');
});

Route::post('upload_file', [UploadController::class, 'upload_file'])->middleware('auth:sanctum');
