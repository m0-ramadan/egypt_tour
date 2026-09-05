<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\CategoryResource;
use App\Http\Resources\Website\CategoryWithProductResource;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {

            $query = Category::where('status_id', 1)->orderBy('order', 'asc');

            switch ($request->get('type')) {
                case 'parent':
                    $query->whereNull('parent_id');
                    $resource = CategoryResource::class;
                    break;
                case 'child':
                    $query->whereNotNull('parent_id');
                    $resource = CategoryWithProductResource::class;
                    break;
                default:
                    $resource = CategoryResource::class;

                    break;
            }

            $categories = $query->get();

            return $this->success($resource::collection($categories), 'تم جلب الأقسام بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب الأقسام', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return $this->error('القسم غير موجود', 404);
            }

            return $this->success(new CategoryWithProductResource($category), 'تم جلب بيانات القسم بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب بيانات القسم', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
