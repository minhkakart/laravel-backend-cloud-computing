<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;

class UploadController extends Controller
{
    //
    public function index()
    {
        return view('upload');
    }

    /**
     * Upload a file.
     *
     * @param string $bucketName The name of your Cloud Storage bucket.
     *        (e.g. 'my-bucket')
     * @param string $objectName The name of your Cloud Storage object.
     *        (e.g. 'my-object')
     * @param string $source The path to the file to upload.
     *        (e.g. '/path/to/your/file')
     */
    function upload_object(Request $request): void
    {
        // $storage = new StorageClient([
        //     'suppressKeyFileNotice' => true,
        //     'key' => env('GOOGLE_CLOUD_TRANSLATE_API_KEY')
        //     ]);
        // $bucketName = 'video-ai-example-1';
        // $image_name = $request->file('image')->getClientOriginalName();
        // $file_temp_path = $request->file('image')->getPathname();
        // $file = fopen($file_temp_path, 'r');
        // $bucket = $storage->bucket($bucketName);
        // $object = $bucket->upload($file, [
        //     'name' => $image_name
        // ]);
        // $gcsUri = $object->gcsUri();
        // printf('Uploaded %s to %s' . PHP_EOL, $file_temp_path, $gcsUri);
        dd($request->file('image'));
    }
}
