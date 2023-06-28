<?php

namespace App\Http\Controllers;


use App\Models\Category;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Validator;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category",
     *     @OA\Response(response="200", description="List Categories.")
     * )
     */
    public function index()
    {
        $list = Category::all();
        return response()->json($list, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(response="200",
     *      description="List Categories."
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="Категорії не знайдено"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизований"
     *     )
     * )
     */
    public function getById($id)
    {
        $category = Category::findorFail($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/category",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="image",
     *                     type="file"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $message = array(
            'name.required' => "Вкажіть назву категорії",
            'image.required' => "Вкажіть фото категорії",
            'description.required' => "Вкажіть опис категорії",
        );
        $validator = Validator::make($input, [
            'name' => 'required',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,svg',
            'description' => 'required'
        ], $message);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400,
                ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $input = $this->imageCreate($image, $input);

        }
        $category = Category::create($input);
        return response()->json($category, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/category/edit/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                   @OA\Property(
     *                       property="image",
     *                       type="file"
     *                   ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Edit Category.")
     * )
     */
    public function update($id, Request $request)
    {
        $category = Category::findorFail($id);;
        $input = $request->all();
        $message = array(
            'name.required' => "Вкажіть назву категорії",
            'image.required' => "Вкажіть фото категорії",
            'description.required' => "Вкажіть опис категорії",
        );
        $validator = Validator::make($input, [
            'name' => 'required',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,svg',
            'description' => 'required'
        ], $message);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400,
                ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }
        if ($request->hasFile('image')) {
            $image = $input['image'];

            $input = $this->imageCreate($image, $input);
            unlink(public_path('uploads/') . '150x150_' . $category['image']);
            unlink(public_path('uploads/') . $category['image']);
        }
        $category->update($input);
        return response()->json($category, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Delete(
     *     path="/api/category/delete/{id}",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успішне видалення категорії"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категорії не знайдено"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизований"
     *     )
     * )
     */
    public function delete($id)
    {
        $category = Category::findOrFail($id);
        if (file_exists(public_path('uploads') .'/'. $category['image']))
        unlink(public_path('uploads') .'/'. $category['image']);
        if (file_exists(public_path('uploads') .'/'. '150x150_' . $category['image']))
            unlink(public_path('uploads') .'/'. '150x150_' . $category['image']);
        $category->delete();
        return 204;
    }

    /**
     * @param $image
     * @param array $input
     * @return array
     */
    public function imageCreate($image, array $input): array
    {
        $destinationPath = public_path('uploads');

        $imageName = uniqid() . '.' . $image->getClientOriginalExtension();

        $imgFile = Image::make($image);

        $imgFile->resize(150, 150, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . '150x150_' . $imageName);

        $image->move($destinationPath, $imageName);
        $input['image'] = $imageName;
        return $input;
    }
}
