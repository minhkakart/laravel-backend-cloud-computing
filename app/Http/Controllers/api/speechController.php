<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\SpeechClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class speechController extends Controller
{
    public function speechAi(Request $request)
    {
        $audioFile = $request->input('gcs_uri');

        // Khởi tạo SpeechClient
        $speechClient = new SpeechClient([
            'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        ]);

        // Tạo một đối tượng RecognitionAudio từ file audio
        $audio = new RecognitionAudio(['uri' => $audioFile]);

        // Thiết lập các thông số cho quá trình nhận dạng
        $config = (new RecognitionConfig())
            ->setEncoding(RecognitionConfig\AudioEncoding::MP3)
            ->setSampleRateHertz(16000)
            ->setLanguageCode('en-US');

        // Gửi yêu cầu nhận dạng
        $response = $speechClient->longRunningRecognize($config, $audio);
        $response->pollUntilComplete();
        $results = [];
        $ketqua = [];

        if ($response->operationSucceeded()) {
            // dd($response->getResult());
            // Lặp qua các kết quả nhận dạng
            foreach ($response->getResult()->getResults() as $result) {
                $results = $result->getAlternatives();
                foreach ($results as $re) {
                    $ketqua[] = $re->getTranscript();
                }
                // echo 'Text: ' . $transcript . PHP_EOL;
            }

            $user_id = Auth::id();
            $user_activities = Activity::firstOrNew([
                'user_id' => $user_id,
                'api' => 'speech_to_text'
            ], [
                'count' => 0
            ]);
            $user_activities->save();
            $user_activities->increment('count');

            return response()->json(['speechdetect' => $ketqua]);
        } else {
            $error = $response->getError();
            return response()->json(['error' => $error])->setStatusCode(500);
        }
    }
}
