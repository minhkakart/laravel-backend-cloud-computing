<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Translate\V2\TranslateClient;

class CloudTranslateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $translate = new TranslateClient([
            'suppressKeyFileNotice' => true,
            'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        ]);
        $languages = $translate->localizedLanguages([
            'target' => 'vi'
        ]);
        return $languages;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return 'create';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
