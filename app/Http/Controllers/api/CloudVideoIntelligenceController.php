<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

class CloudVideoIntelligenceController extends Controller
{
    /**
     * 
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     **/
    public function labelDetection(Request $request)
    {
        $videoIntelligenceServiceClient = new VideoIntelligenceServiceClient();

        $inputUri = "gs://video-ai-example-1/2023-12-23 16-56-28.mkv";

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
            return response()->json($respone);
        } else {
            $error = $operationResponse->getError();
            return response()->json($error);
            // echo "error: " . $error->getMessage() . PHP_EOL;

        }
    }
}
