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
        // $video = $request->file('video');
        // if ($video) {
            if (true) {
            // Lưu tệp video vào storage
            // $videoPath = $video->store('videos', 'public');

            // Đường dẫn đầy đủ đến tệp đã lưu
            // $fullPath = storage_path('app/public/' . $videoPath);

            // Gọi API từ Google Cloud để nhận dạng explicit content
            $videoIntelligenceServiceClient = new VideoIntelligenceServiceClient(
                [
                    'key'=>'AIzaSyAEUVhT079TLkIiDe2XmpcoifaIOQvQdLM'
                ]
            );
            $features = [Feature::EXPLICIT_CONTENT_DETECTION];
            $operationResponse = $videoIntelligenceServiceClient->annotateVideo([
                // 'inputUri' => 'gs://your-bucket/' . $videoPath, // Thay thế your-bucket bằng tên bucket của bạn
                'inputUri' => 'gs://video-ai-example-1/Genshin_Impact_2023-11-17_23-05-47.1718551286.mp4',
                'features' => $features
            ]);

            $operationResponse->pollUntilComplete();
            if ($operationResponse->operationSucceeded()) {
                $result = $operationResponse->getResult();
                // dd($result);
                $frames = [];
                foreach ($result->getAnnotationResults()[0]->getExplicitAnnotation()->getFrames() as $frame) {

                    if($likelihood = $frame->getPornographyLikelihood() == 0){
                        $frameTime = $frame->getTimeOffset()->getSeconds() + $frame->getTimeOffset()->getNanos() / 1e9;
                        $frames[] = [
                            'time' => $frameTime,
                            'pornography' => $likelihood,
                        ];
                    }
                    // $likelihood = $frame->getPornographyLikelihood();
                    
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
