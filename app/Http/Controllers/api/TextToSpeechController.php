<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Support\Facades\Storage;

class TextToSpeechController extends Controller
{
    //
    // public function upload(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:txt|max:2048',
    //     ]);

    //     $file = $request->file('file');
    //     $text = file_get_contents($file->getRealPath());

    //     $outputFilePath = public_path('output.mp3');
    //     $this->synthesize_text($text, $outputFilePath);

    //     return view('TextToSpeech.textToSpeech', ['audioFile' => 'output.mp3']);
    // }

    public function synthesize_text(Request $request)
    {
        $client = new TextToSpeechClient();
        // $text = $request->input('text');
        $text = "Xin chào, đây là nhóm mười một";

        $input_text = (new SynthesisInput())->setText($text);

        $voice = (new VoiceSelectionParams())
            ->setLanguageCode('en-US')
            ->setSsmlGender(SsmlVoiceGender::FEMALE);

        $audioConfig = (new AudioConfig())
            ->setAudioEncoding(AudioEncoding::MP3);

        $response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();
        $file_path = public_path('text' . time() . '.mp3');
        // Storage::put($file_path, $audioContent);

        file_put_contents($file_path, $audioContent);


        $client->close();

        return response()->file($file_path);
    }
}
