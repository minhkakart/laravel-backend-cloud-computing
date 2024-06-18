<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Http\Request;

class CloudVisionController extends Controller
{
    //
    public function landmarkDetection(Request $resquest)
    {
        $image_url = $resquest->input('gcs_uri');


        $client = new ImageAnnotatorClient();

        // Đọc nội dung của hình ảnh, nhận diện địa điểm.
        // $image = file_get_contents('/xampp/htdocs/bai1/eiffel.jpg');
        $response = $client->landmarkDetection($image_url);

        // Hiển thị kết quả nhận diện địa điểm.
        $annotations = $response->getLandmarkAnnotations();
        $result = [];
        foreach ($annotations as $annotation) {
            // echo "Landmark: " . $annotation->getDescription() . PHP_EOL;
            $result[] =  $annotation->getDescription();
        }

        $client->close();

        return response()->json(['descriptions' => $result]);
    }
}
