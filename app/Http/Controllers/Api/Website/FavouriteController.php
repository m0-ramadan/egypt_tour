<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Favourite;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ProductResource;

class FavouriteController extends Controller
{
    use ApiResponseTrait;

    /**
     * 🔹 عرض قائمة المفضلة للمستخدم
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $favourites = Favourite::where('user_id', $user->id)
                ->with('product')
                ->latest()
                ->get()
                ->pluck('product');

            return $this->success(ProductResource::collection($favourites), 'تم جلب المفضلة بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب المفضلة', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 إضافة منتج إلى المفضلة أو إزالته (toggle)
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $productId = $request->product_id;

            $favourite = Favourite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($favourite) {
                // لو المنتج موجود بالفعل → احذفه
                $favourite->delete();
                return $this->success(null, 'تم إزالة المنتج من المفضلة');
            }

            // غير موجود → أضفه
            Favourite::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);

            return $this->success(null, 'تمت إضافة المنتج إلى المفضلة');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء تحديث المفضلة', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 حذف منتج من المفضلة
     */
    public function destroy(Request $request, $productId)
    {
        try {
            $user = $request->user();

            $favourite = Favourite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if (!$favourite) {
                return $this->error('المنتج غير موجود في المفضلة', 404);
            }

            $favourite->delete();

            return $this->success(null, 'تم حذف المنتج من المفضلة بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء حذف المنتج من المفضلة', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
