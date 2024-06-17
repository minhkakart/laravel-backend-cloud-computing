<?php

namespace App\Http\Controllers;

use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Illuminate\Http\Request;

class TestApiController extends Controller
{
    public function test(Request $request)
    {
        $videoIntelligenceServiceClient = new VideoIntelligenceServiceClient([
            'suppressKeyFileNotice' => true,
            'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        ]);

        $inputUri = 'gs://video-ai-example-1/Genshin_Impact_2023-11-17_23-10-46.1718607115.mp4';
        // $inputUri = $request->input('gcs_uri');

        $features = [
            Feature::TEXT_DETECTION,
        ];
        $operationResponse = $videoIntelligenceServiceClient->annotateVideo([
            'inputUri' => $inputUri,
            'features' => $features
        ]);
        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $results = $operationResponse->getResult();
            dd($results);
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

            // $user_id = Auth::id();
            // $user_activities = Activity::firstOrNew([
            //     'user_id' => $user_id,
            //     'api' => 'face-detection'
            // ], [
            //     'count' => 0
            // ]);
            // $user_activities->increment('count');
            // $user_activities->save();

            return response()->json($respone);
        } else {
            $error = $operationResponse->getError();
            return response()->json($error)->setStatusCode(500);
        }
    }
}
