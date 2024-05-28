<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Translate\V2\TranslateClient;

class CloudTranslateController extends Controller
{
    static $translate;
    public function __construct() {
        CloudTranslateController::$translate = new TranslateClient([
            'suppressKeyFileNotice' => true,
            'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        ]);
    }

    /**
     * Get list of supported languages
     *
     * GET /api/cloud-translate/list-language?target=vi
     *
     * @param Request $request get target language
     * @return array
     **/
    public function listLanguage(Request $request)
    {
        $target = $request->query('target');
        $option = [];
        if ($target) {
            $option['target'] = $target;
        }
        
        return CloudTranslateController::$translate->localizedLanguages($option);
    }

    /**
     * Translate text
     *
     * POST /api/cloud-translate/translate
     *
     * @param Request $request get source text and target language
     * @return array
     **/
    public function translate(Request $request)
    {
        $source = $request->input('source');
        $target = $request->input('target');

        $result = CloudTranslateController::$translate->translate($source, [
            'target' => $target
        ]);

        return $result;
    }

    /**
     * Detect language
     *
     * POST /api/cloud-translate/detect-language
     *
     * @param Request $request Description
     * @return array
     **/
    public function detectLanguage(Request $request)
    {
        $source = $request->input('source');

        $result = CloudTranslateController::$translate->detectLanguage($source);

        return $result;
    }
    
}
