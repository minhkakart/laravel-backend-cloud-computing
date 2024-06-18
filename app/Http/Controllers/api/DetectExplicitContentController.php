<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Illuminate\Http\Request;

class DetectExplicitContentController extends Controller
{
    //
    public function detectExplicitContent(Request $request)
    {
        $gcs_uri = $request->input('gcs_uri');
        if ($gcs_uri) {
            
            $videoIntelligenceServiceClient = new VideoIntelligenceServiceClient(
                [
                    'key'=>'AIzaSyAEUVhT079TLkIiDe2XmpcoifaIOQvQdLM'
                ]
            );
            $features = [Feature::EXPLICIT_CONTENT_DETECTION];
            $operationResponse = $videoIntelligenceServiceClient->annotateVideo([
                'inputUri' => $gcs_uri,
                'features' => $features
            ]);

            $operationResponse->pollUntilComplete();
            if ($operationResponse->operationSucceeded()) {
                $result = $operationResponse->getResult();
                // dd($result);
                $frames = [];
                foreach ($result->getAnnotationResults()[0]->getExplicitAnnotation()->getFrames() as $frame) {
                    if($frame->getPornographyLikelihood() == 0){
                        $frameTime = $frame->getTimeOffset()->getSeconds() + $frame->getTimeOffset()->getNanos() / 1e9;
                        $frames[] = [
                            'time' => $frameTime,
                            'pornography' => 'Video is likely contains pornography or violence',
                        ];
                    }   
                }
                return response()->json(['frames' => $frames]);
            } else {
                $error = $operationResponse->getError();
                
                return response()->json(['error' => $error])->setStatusCode(500);
            }
        } else {
            return response()->json(['error' => 'Please upload a video file.'])->setStatusCode(500);
        }
    }
}
