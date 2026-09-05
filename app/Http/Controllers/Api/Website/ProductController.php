<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Resources\Website\ColorResource;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\MaterialResource;
use App\Http\Resources\Website\ProductResource;
use App\Models\Color;

class ProductController extends Controller
{
    use ApiResponseTrait;


    public function index(Request $request)
    {
        try {
            $query = Product::with([
                'category',
                'discount',
                'features',
                'reviews',
            ])->where('status_id', 1);

            $products = $query
                ->filtered($request)
                ->searched($request->get('search'))
                ->sorted($request)
                ->paginate($request->get('per_page', 10));
            return $this->paginated(
                ProductResource::collection($products),
                'تم جلب المنتجات بنجاح'
            );
        } catch (\Throwable $e) {
            return $this->error('حدث خطأ أثناء جلب المنتجات', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }


    public function show($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->error('المنتج غير موجود', 404);
            }

            return $this->success(new ProductResource($product), 'تم جلب بيانات المنتج بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب بيانات المنتج', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }


}
