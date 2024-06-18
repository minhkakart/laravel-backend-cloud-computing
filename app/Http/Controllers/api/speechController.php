<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\SpeechClient;
use Illuminate\Http\Request;

class speechController extends Controller
{
    public function speechAi(Request $request)
    {
        $audioFile = $request->input('gcs_uri');

        $speechClient = new SpeechClient();
        //dịch 1 đoạn nhạc thành text
        $audioFile = '/xampp/htdocs/bai1/happy.mp3';

        // Khởi tạo SpeechClient

        // Đọc dữ liệu từ file audio

        $audio = (new RecognitionAudio())
            ->setContent(file_get_contents($audioFile));

        // Thiết lập các thông số cho quá trình nhận dạng
        $config = (new RecognitionConfig())
            ->setEncoding(RecognitionConfig\AudioEncoding::MP3)
            ->setSampleRateHertz(16000)
            ->setLanguageCode('en-US');

        // Gửi yêu cầu nhận dạng
        $response = $speechClient->recognize($config, $audio);
        $results =[];
        $ketqua = [];
        // dd($response);
        // Lặp qua các kết quả nhận dạng
        foreach ($response->getResults() as $result) {
            // $transcript = $result->getAlternatives()[0]->getTranscript();
            // $results[] = $result->getAlternatives()[0]->getTranscript();
            $results = $result->getAlternatives();

            foreach($results as $re){
                $ketqua[] = $re->getTranscript();

            }
            // echo 'Text: ' . $transcript . PHP_EOL;
        }

        // Đóng SpeechClient
        $speechClient->close();
        return response()->json(['speechdetect' => $ketqua]);
    }
}
