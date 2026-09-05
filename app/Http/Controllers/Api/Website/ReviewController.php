<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ReviewResource;
use App\Http\Requests\Website\Review\StoreReviewRequest;
use App\Http\Requests\Website\Review\UpdateReviewRequest;


class ReviewController extends Controller
{
    use ApiResponseTrait;

    /**
     * 🔹 عرض جميع التقييمات مع الفلترة والترتيب
     */
    public function index(Request $request)
    {
        try {
            // التحقق من وجود منتج محدد
            $productId = $request->get('product_id');

            $query = Review::with(['product', 'user'])
                ->latest();

            // فلتر حسب المنتج
            if ($productId) {
                $query->where('product_id', $productId);
            }

            // فلتر حسب المستخدم
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // فلتر حسب التقييم
            if ($request->has('rating')) {
                $rating = (int) $request->rating;
                if (in_array($rating, [1, 2, 3, 4, 5])) {
                    $query->where('rating', $rating);
                }
            }

            // فلتر حسب نطاق التقييم
            if ($request->has('min_rating')) {
                $query->where('rating', '>=', (int) $request->min_rating);
            }

            if ($request->has('max_rating')) {
                $query->where('rating', '<=', (int) $request->max_rating);
            }

            // فلتر حسب التاريخ
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // ترتيب حسب
            if ($request->has('sort_by')) {
                $sortBy = $request->sort_by;
                $direction = $request->get('sort_direction', 'desc');

                $allowedSort = ['created_at', 'rating', 'updated_at'];
                if (in_array($sortBy, $allowedSort)) {
                    $query->orderBy($sortBy, $direction);
                }
            }

            // البحث في التعليقات
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            }

            // هل يحتوي على تعليقات فقط
            if ($request->boolean('has_comment')) {
                $query->whereNotNull('comment')->where('comment', '!=', '');
            }

            // الباجات
            $perPage = $request->get('per_page', 15);
            $reviews = $query->paginate($perPage);

            // إحصائيات إضافية للمنتج
            $stats = null;
            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $stats = [
                        'average_rating' => round($product->average_rating, 1),
                        'total_reviews' => $product->reviews()->count(),
                        'rating_distribution' => $this->getRatingDistribution($productId),
                    ];
                }
            }

            return $this->success([
                'reviews' => ReviewResource::collection($reviews),
                'pagination' => [
                    'total' => $reviews->total(),
                    'per_page' => $reviews->perPage(),
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'from' => $reviews->firstItem(),
                    'to' => $reviews->lastItem(),
                ],
                'stats' => $stats,
                'filters' => $request->only([
                    'product_id',
                    'user_id',
                    'rating',
                    'min_rating',
                    'max_rating',
                    'search'
                ]),
            ], 'تم جلب التقييمات بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب التقييمات', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 عرض تقييم معين
     */
    public function show($id)
    {
        try {
            $review = Review::with(['product', 'user'])->findOrFail($id);

            return $this->success(
                new ReviewResource($review),
                'تم جلب التقييم بنجاح'
            );
        } catch (\Exception $e) {
            return $this->error('التقييم غير موجود', 404);
        }
    }

    /**
     * 🔹 إنشاء تقييم جديد
     */
    public function store(StoreReviewRequest $request)
    {
        try {
            // التحقق من أن المستخدم لم يقم بتقييم هذا المنتج من قبل
            $existingReview = Review::where('user_id', auth()->id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingReview) {
                return $this->error('لقد قمت بتقييم هذا المنتج من قبل', 400);
            }

            // التحقق من أن المستخدم قد طلب هذا المنتج (اختياري)
            // يمكنك إضافة هذا الشرط إذا أردت

            // إنشاء التقييم
            $review = Review::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // تحديث متوسط تقييمات المنتج
            $this->updateProductRating($request->product_id);

            return $this->success(
                new ReviewResource($review->load(['product', 'user'])),
                'تم إضافة التقييم بنجاح',
                201
            );
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء إضافة التقييم', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 تحديث التقييم
     */
    public function update(UpdateReviewRequest $request, $id)
    {
        try {
            $review = Review::findOrFail($id);

            // التحقق من أن المستخدم هو صاحب التقييم
            if ($review->user_id !== auth()->id() && !auth()->user()->is_admin) {
                return $this->error('غير مسموح لك بتعديل هذا التقييم', 403);
            }

            // تحديث التقييم
            $review->update([
                'rating' => $request->rating ?? $review->rating,
                'comment' => $request->comment ?? $review->comment,
            ]);

            // تحديث متوسط تقييمات المنتج
            $this->updateProductRating($review->product_id);

            return $this->success(
                new ReviewResource($review->load(['product', 'user'])),
                'تم تحديث التقييم بنجاح'
            );
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء تحديث التقييم', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 حذف التقييم
     */
    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            // التحقق من أن المستخدم هو صاحب التقييم أو أدمن
            if ($review->user_id !== auth()->id() && !auth()->user()->is_admin) {
                return $this->error('غير مسموح لك بحذف هذا التقييم', 403);
            }

            $productId = $review->product_id;
            $review->delete();

            // تحديث متوسط تقييمات المنتج بعد الحذف
            $this->updateProductRating($productId);

            return $this->success(
                null,
                'تم حذف التقييم بنجاح'
            );
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء حذف التقييم', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 الحصول على تقييمات المستخدم الحالي
     */
    public function myReviews(Request $request)
    {
        try {
            $query = Review::with(['product', 'user'])
                ->where('user_id', auth()->id())
                ->latest();

            // فلتر حسب المنتج
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // فلتر حسب التقييم
            if ($request->has('rating')) {
                $query->where('rating', (int) $request->rating);
            }

            // الباجات
            $perPage = $request->get('per_page', 15);
            $reviews = $query->paginate($perPage);

            // إحصائيات
            $stats = [
                'total_reviews' => $reviews->total(),
                'average_rating' => round($query->avg('rating'), 1),
            ];

            return $this->success([
                'reviews' => ReviewResource::collection($reviews),
                'stats' => $stats,
                'pagination' => [
                    'total' => $reviews->total(),
                    'per_page' => $reviews->perPage(),
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                ],
            ], 'تم جلب تقييماتك بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب تقييماتك', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 الحصول على تقييمات منتج معين
     */
    public function productReviews($productId, Request $request)
    {
        try {
            $product = Product::findOrFail($productId);

            $query = Review::with(['user'])
                ->where('product_id', $productId)
                ->latest();

            // فلتر حسب التقييم
            if ($request->has('rating')) {
                $rating = (int) $request->rating;
                if (in_array($rating, [1, 2, 3, 4, 5])) {
                    $query->where('rating', $rating);
                }
            }

            // ترتيب حسب
            if ($request->has('sort_by')) {
                $sortBy = $request->sort_by;
                $direction = $request->get('sort_direction', 'desc');

                if ($sortBy === 'rating') {
                    $query->orderBy('rating', $direction);
                } elseif ($sortBy === 'date') {
                    $query->orderBy('created_at', $direction);
                }
            }

            // الباجات
            $perPage = $request->get('per_page', 10);
            $reviews = $query->paginate($perPage);

            // إحصائيات المنتج
            $stats = [
                'average_rating' => round($product->average_rating, 1),
                'total_reviews' => $product->reviews()->count(),
                'rating_distribution' => $this->getRatingDistribution($productId),
            ];

            // هل قام المستخدم الحالي بتقييم المنتج
            $userReview = null;
            if (auth()->check()) {
                $userReview = Review::where('product_id', $productId)
                    ->where('user_id', auth()->id())
                    ->first();
            }

            return $this->success([
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image,
                ],
                'stats' => $stats,
                'user_review' => $userReview ? new ReviewResource($userReview) : null,
                'reviews' => ReviewResource::collection($reviews),
                'pagination' => [
                    'total' => $reviews->total(),
                    'per_page' => $reviews->perPage(),
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                ],
            ], 'تم جلب تقييمات المنتج بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب تقييمات المنتج', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 تحديث متوسط تقييم المنتج
     */
    private function updateProductRating($productId)
    {
        try {
            $product = Product::find($productId);
            if ($product) {
                // متوسط التقييم محسوب في الموديل من خلال Accessor
                // يمكنك تحديث حقل cached إذا كان موجوداً
                // $average = $product->reviews()->avg('rating');
                // $product->update(['average_rating' => $average]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating product rating: ' . $e->getMessage());
        }
    }

    /**
     * 🔹 الحصول على توزيع التقييمات لمنتج
     */
    private function getRatingDistribution($productId)
    {
        $distribution = [];

        for ($i = 5; $i >= 1; $i--) {
            $count = Review::where('product_id', $productId)
                ->where('rating', $i)
                ->count();

            $distribution[$i] = [
                'stars' => $i,
                'count' => $count,
                'percentage' => 0, // سيتم حسابها لاحقاً
            ];
        }

        $total = array_sum(array_column($distribution, 'count'));

        if ($total > 0) {
            foreach ($distribution as $i => $data) {
                $distribution[$i]['percentage'] = round(($data['count'] / $total) * 100, 1);
            }
        }

        return $distribution;
    }
}
