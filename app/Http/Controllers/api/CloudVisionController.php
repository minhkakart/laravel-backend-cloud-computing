<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Likelihood;
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

    public function textDetection(Request $request)
    {
        $image_url = $request->input('gcs_uri');
        $client = new ImageAnnotatorClient();

        // Đọc nội dung của hình ảnh, nhận diện chữ.
        // $image = file_get_contents('/xampp/htdocs/bai1/mu.png');
        $response = $client->textDetection($image_url);

        // Hiển thị kết quả nhận diện chữ.
        $annotations = $response->getTextAnnotations();
        $result = [];
        foreach ($annotations as $annotation) {
            $result[] = $annotation->getDescription();
        }

        $client->close();
        return response()->json(['textdetect' => $result]);
    }

    public function faceDetection(Request $request)
    {
        $image_url = $request->input('gcs_uri');

        $client = new ImageAnnotatorClient();

        $response = $client->faceDetection($image_url);
        $faceAnnotations = $response->getFaceAnnotations();

        $faces = [];
        if ($faceAnnotations) {
            foreach ($faceAnnotations as $faceAnnotation) {
                $likelihoods = [
                    'joyLikelihood' => Likelihood::name($faceAnnotation->getJoyLikelihood()),
                    'sorrowLikelihood' => Likelihood::name($faceAnnotation->getSorrowLikelihood()),
                    'angerLikelihood' => Likelihood::name($faceAnnotation->getAngerLikelihood()),
                    'surpriseLikelihood' => Likelihood::name($faceAnnotation->getSurpriseLikelihood()),
                    'underExposedLikelihood' => Likelihood::name($faceAnnotation->getUnderExposedLikelihood()),
                    'blurredLikelihood' => Likelihood::name($faceAnnotation->getBlurredLikelihood()),
                    'headwearLikelihood' => Likelihood::name($faceAnnotation->getHeadwearLikelihood()),
                ];
                $faces[] = $likelihoods;
            }
        }

        $client->close();

        return response()->json(['faces' => $faces]);
    }

    public function labelDetection(Request $request)
    {
        $image_url = $request->input('gcs_uri');

        $client = new ImageAnnotatorClient();

        $response = $client->labelDetection($image_url);
        $labelAnnotations = $response->getLabelAnnotations();

        $labels = [];
        if ($labelAnnotations) {
            foreach ($labelAnnotations as $labelAnnotation) {
                $labels[] = $labelAnnotation->getDescription();
            }
        }

        $client->close();

        return response()->json(['labels' => $labels]);
    }

    public function safeSearchDetection(Request $request)
    {
        $image_url = $request->input('gcs_uri');

        $client = new ImageAnnotatorClient();

        $response = $client->safeSearchDetection($image_url);
        $safeSearchAnnotation = $response->getSafeSearchAnnotation();

        $safeSearch = [];
        if ($safeSearchAnnotation) {
            $safeSearch = [
                'spoofLikelihood' => Likelihood::name($safeSearchAnnotation->getSpoof()),
                'medicalLikelihood' => Likelihood::name($safeSearchAnnotation->getMedical()),
                'violenceLikelihood' => Likelihood::name($safeSearchAnnotation->getViolence()),
                'racyLikelihood' => Likelihood::name($safeSearchAnnotation->getRacy()),
            ];
        }

        $client->close();

        return response()->json(['safeSearch' => $safeSearch]);
    }

    public function logoDetection(Request $request)
    {
        $image_url = $request->input('gcs_uri');

        $client = new ImageAnnotatorClient();

        $response = $client->logoDetection($image_url);
        $logoAnnotations = $response->getLogoAnnotations();

        $logos = [];
        if ($logoAnnotations) {
            foreach ($logoAnnotations as $logoAnnotation) {
                $logos[] = $logoAnnotation->getDescription();
            }
        }

        $client->close();

        return response()->json(['logos' => $logos]);
    }

    public function imagePropertyDetection(Request $request)
    {
        $image_url = $request->input('gcs_uri');

        $client = new ImageAnnotatorClient();

        $response = $client->imagePropertiesDetection($image_url);
        $imagePropertiesAnnotation = $response->getImagePropertiesAnnotation();

        $properties = [];
        if ($imagePropertiesAnnotation) {
            $dominantColors = $imagePropertiesAnnotation->getDominantColors()->getColors();
            foreach ($dominantColors as $colorInfo) {
                $color = $colorInfo->getColor();
                $properties[] = [
                    'red' => $color->getRed(),
                    'green' => $color->getGreen(),
                    'blue' => $color->getBlue(),
                ];
            }
        }

        $client->close();

        return response()->json(['properties' => $properties]);
    }
}