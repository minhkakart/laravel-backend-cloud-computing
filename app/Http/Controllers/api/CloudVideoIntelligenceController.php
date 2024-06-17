<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;
use Illuminate\Support\Facades\Auth;

class CloudVideoIntelligenceController extends Controller
{

    public function labelDetection(Request $request)
    {
        $videoIntelligenceServiceClient = new VideoIntelligenceServiceClient([
            'suppressKeyFileNotice' => true,
            'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        ]);

        // $inputUri = "gs://video-ai-example-1/2023-12-23 16-56-28.mkv";
        $inputUri = $request->input('gcs_uri');

        $features = [
            Feature::LABEL_DETECTION,
        ];
        $operationResponse = $videoIntelligenceServiceClient->annotateVideo([
            'inputUri' => $inputUri,
            'features' => $features
        ]);
        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $results = $operationResponse->getResult();
            $respone = [];
            $anotationResults = [];
            foreach ($results->getAnnotationResults() as $result) {
                // echo 'Segment labels' . PHP_EOL;
                $segmentLabels = [];
                foreach ($result->getSegmentLabelAnnotations() as $labelAnnotation) {
                    // echo "Label: " . $labelAnnotation->getEntity()->getDescription()
                    // . PHP_EOL;
                    $segmentLabels[] = $labelAnnotation->getEntity()->getDescription();
                }
                // echo 'Shot labels' . PHP_EOL;
                $shotLabels = [];
                foreach ($result->getShotLabelAnnotations() as $labelAnnotation) {
                    // echo "Label: " . $labelAnnotation->getEntity()->getDescription()
                    // . PHP_EOL;
                    $shotLabels[] = $labelAnnotation->getEntity()->getDescription();
                }
                // echo 'Frame labels' . PHP_EOL;
                $frameLabels = [];
                foreach ($result->getFrameLabelAnnotations() as $labelAnnotation) {
                    // echo "Label: " . $labelAnnotation->getEntity()->getDescription()
                    // . PHP_EOL;
                    $frameLabels[] = $labelAnnotation->getEntity()->getDescription();
                }
                $anotationResults[] = [
                    'segment_labels' => $segmentLabels,
                    'shot_labels' => $shotLabels,
                    'frame_labels' => $frameLabels
                ];
            }
            $respone['anotation_results'] = $anotationResults;

            $user_id = Auth::id();
            $user_activities = Activity::firstOrNew([
                'user_id' => $user_id,
                'api' => 'labels-detection'
            ], [
                'count' => 0
            ]);
            $user_activities->increment('count');
            $user_activities->save();

            return response()->json($respone);
        } else {
            $error = $operationResponse->getError();
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function faceDetection(Request $request)
    {
        $videoIntelligenceServiceClient = new VideoIntelligenceServiceClient([
            'suppressKeyFileNotice' => true,
            'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        ]);

        // $inputUri = 'gs://video-ai-example-1/WIN_20240515_19_42_50_Pro.mp4';
        $inputUri = $request->input('gcs_uri');

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

            $user_id = Auth::id();
            $user_activities = Activity::firstOrNew([
                'user_id' => $user_id,
                'api' => 'face-detection'
            ], [
                'count' => 0
            ]);
            $user_activities->increment('count');
            $user_activities->save();

            return response()->json($respone);
        } else {
            $error = $operationResponse->getError();
            return response()->json($error)->setStatusCode(500);
        }
    }
}
