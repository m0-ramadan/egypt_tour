<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\FavoriteResource;
use App\Traits\ApiResponseTrait;

class FavoriteController extends Controller
{
    use ApiResponseTrait;

    /**
     * 🟢 Add/Remove Favorite (Toggle)
     */
    public function toggle(Request $request)
    {
        $validator = validator($request->all(), [
            'product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user_id = auth()->id();

        $favorite = Favorite::where('user_id', $user_id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($favorite) {
            // 🔴 Remove Favorite
            $favorite->delete();

            return $this->successResponse([
                'is_favorite' => false
            ], 'تم الحذف من المفضلة');
        }

        // 🟢 Add Favorite
        $newFavorite = Favorite::create([
            'user_id' => $user_id,
            'product_id' => $request->product_id
        ]);

        return $this->successResponse([
            'is_favorite' => true,
            'favorite' => new FavoriteResource($newFavorite)
        ], 'تمت الإضافة إلى المفضلة');
    }

    /**
     * 🟡 Get All User Favorites
     */
    public function index()
    {
        $favorites = Favorite::with('product')
            ->where('user_id', auth()->id())
            ->get();

        return $this->successResponse(
            FavoriteResource::collection($favorites),
            'تم جلب المفضلة بنجاح'
        );
    }
}
