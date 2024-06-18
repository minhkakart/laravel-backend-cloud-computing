<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Illuminate\Support\Facades\Auth;

class TextToSpeechController extends Controller
{
    public function synthesize_text(Request $request)
    {
        $client = new TextToSpeechClient();
        if ($request->has('text')) {
            $text = $request->text;
        } else {
            return response()->json([
                'message' => 'Text is required'
            ])->setStatusCode(400);
        }

        $input_text = (new SynthesisInput())->setText($text);

        $voice = (new VoiceSelectionParams())
            ->setLanguageCode('vi-VN')
            ->setSsmlGender(SsmlVoiceGender::FEMALE);

        $audioConfig = (new AudioConfig())
            ->setAudioEncoding(AudioEncoding::LINEAR16);

        $response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();
        $client->close();

        $user_id = Auth::id();
            $user_activities = Activity::firstOrNew([
                'user_id' => $user_id,
                'api' => 'text_to_speech'
            ], [
                'count' => 0
            ]);
            $user_activities->save();
            $user_activities->increment('count');

        return response()->json([
            'audioContent' => base64_encode($audioContent),
        ]);
    }
}
