<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
class ImageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/getImage/{image}",
     *     operationId="getImage",
     *     tags={"Image"},
     *     summary="Retrieve an image by filename",
     *     description="Retrieve an image file based on the provided filename",
     *     @OA\Parameter(
     *         name="image",
     *         required=true,
     *         in="path",
     *         description="Filename of the image to retrieve",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image file retrieved successfully",
     *         @OA\MediaType(
     *             mediaType="image/*"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Image file not found"
     *     )
     * )
     */
    public function getImage($filename)
    {
        $imagePath = public_path('uploads/' . $filename);

        if (file_exists($imagePath)) {
            $fileContents = file_get_contents($imagePath);
            $response = Response::make($fileContents, 200);
            $response->header('Content-Type', 'image/*');
            return $response;
        } else {
            return response()->json(['error' => 'Image not found.'], 404);
        }
    }
}
