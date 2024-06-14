<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Uploaded;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    //
    function upload_file(Request $request)
    {
        $requestFile = $request->file('file');

        // Check file size is less than 100MB
        $file_size = $requestFile->getSize();
        if ($file_size > 100 * 1024 * 1024) {
            return response(['error' => 'File size is too large'], 400);
        }

        // Construct file name
        $file_name = $requestFile->getClientOriginalName();
        $file_ext = $requestFile->getClientOriginalExtension();
        $file_name = str_replace(' ', '_', $file_name);
        $file_name = str_replace($file_ext, now()->getTimestamp(), $file_name) . '.' . $file_ext;
        $file_mime_type = $requestFile->getMimeType();

        // Get file content
        $file_temp_path = $requestFile->getPathname();
        $file = fopen($file_temp_path, 'r');

        // Upload file to Google Cloud Storage
        $bucketName = 'video-ai-example-1';
        $storage = new StorageClient([
            'suppressKeyFileNotice' => true,
            'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        ]);
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($file, [
            'name' => $file_name,
            'contentType' => $file_mime_type
        ]);
        $gcsUri = $object->gcsUri();

        // Get user id
        $user_id = Auth::id();

        // Save to database
        Uploaded::create([
            'user_id' => $user_id,
            'type' => $file_mime_type,
            'gcs_uri' => $gcsUri
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'gcs_uri' => $gcsUri
        ]);
    }
}
