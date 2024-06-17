<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CloudVideoIntelligenceController;
use App\Http\Controllers\api\UploadController;
use App\Mail\TestMail;
use App\Models\User;
use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\CloudTranslateController;


Route::get('email/verify/{id}/{hash}', function (Request $request) {
    $user = User::find($request->route('id'));
    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ])->setStatusCode(404);
    }

    $expires = $request->query('expires');
    if ($expires && ($expires < time())) {
        return response()->json([
            'message' => 'The verification link has expired'
        ])->setStatusCode(400);
    }
    
    if (!$request->hasValidSignature()) {
        return response()->json([
            'message' => 'Invalid signature'
        ])->setStatusCode(400);
    }

    if (!hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
        return response()->json([
            'message' => 'Invalid verification link'
        ])->setStatusCode(400);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Email already verified'
        ]);
    }

    $user->markEmailAsVerified();
    event(new Verified($user));

    // $request->fulfill();

    return response()->json([
        'message' => 'Email verified'
    ]);
})->name('verification.verify');

Route::post('email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return response()->json([
        'message' => 'Email verification link sent'
    ]);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
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

Route::post('test_face', function (Request $request){
    $videoIntelligenceServiceClient = new VideoIntelligenceServiceClient([
        'suppressKeyFileNotice' => true,
        'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
    ]);

    $inputUri = 'gs://video-ai-example-1/WIN_20240515_19_42_50_Pro.mp4';
    // $inputUri = $request->input('gcs_uri');

    $features = [
        Feature::FACE_DETECTION,
    ];
    $operationResponse = $videoIntelligenceServiceClient->annotateVideo([
        'inputUri' => $inputUri,
        'features' => $features
    ]);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $results = $operationResponse->getResult();
        // dd($results);
        $anotationResults = [];
        foreach ($results->getAnnotationResults() as $result) {
            // echo 'Segment labels' . PHP_EOL;
            $face_detection_annotations = [];
            foreach ($result->getFaceDetectionAnnotations() as $faceDetectionAnnotation) {
                $tracks = [];
                foreach ($faceDetectionAnnotation->getTracks() as $track) {
                    $tracks[] = [
                        'segment' => [
                            'start_time_offset' => $track->getSegment()->getStartTimeOffset()->getSeconds() + $track->getSegment()->getStartTimeOffset()->getNanos() / 1000000000,
                            'end_time_offset' => $track->getSegment()->getEndTimeOffset()->getSeconds() + $track->getSegment()->getEndTimeOffset()->getNanos() / 1000000000,
                        ],
                        'confidence' => $track->getConfidence(),
                    ];
                }
                $thumbnail = $faceDetectionAnnotation->getThumbnail();
                $thumbnail = base64_encode($thumbnail);
                $face_detection_annotations[] = [
                    'tracks' => $tracks,
                    'thumbnail' => $thumbnail
                ];
            }

            $anotationResults[] = [
                'face_detection_annotations' => $face_detection_annotations
            ];
        }
        $respone = ['anotation_results' => $anotationResults];
        return response()->json($respone);
    } else {
        $error = $operationResponse->getError();
        return response()->json($error)->setStatusCode(500);
    }
});

Route::get('test_mail', function (Request $request) {
    $to = $request->input('to_email');
    Mail::to($to)->send(new TestMail());
    return response()->json([
        'message' => 'Mail sent successfully'
    ]);
});