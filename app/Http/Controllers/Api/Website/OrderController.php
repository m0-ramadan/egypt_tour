<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\HandlesPaymobPayment;
use App\Http\Resources\Website\OrderResource;
use App\Http\Requests\Website\ApplyCouponRequest;
use App\Http\Requests\Website\CreateOrderRequest;
use App\Http\Resources\Website\OrderDetailsResource;
use App\Http\Resources\Website\PaymentMethodResource;
use App\Models\PaymentMethod;

class OrderController extends Controller
{
    use ApiResponseTrait, HandlesPaymobPayment;

    /**
     * عرض طلبات المستخدم المسجل
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return $this->errorResponse('يجب تسجيل الدخول لعرض الطلبات', 401);
        }

        $orders = Order::with(['items.product', 'items'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            OrderResource::collection($orders)->response()->getData(true),
            'تم جلب الطلبات بنجاح'
        );
    }

    /**
     * إنشاء طلب جديد من السلة
     */
    public function store(CreateOrderRequest $request)
    {
        $user = auth('sanctum')->user();
        $cart = $this->getCurrentCart();

        if ($cart->items()->count() === 0) {
            return $this->errorResponse('السلة فارغة، لا يمكن إنشاء طلب', 400);
        }

        return DB::transaction(function () use ($request, $user, $cart) {


            // تحديد الكوبون لو موجود
            $coupon = null;
            $discountAmount = 0;
            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('orders', 'public');
            }

            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))
                    ->first();

                if (!$coupon || !$coupon->isValidForOrder($cart->total, $user?->id, session()->getId())) {
                    return $this->errorResponse('كوبون الخصم غير صالح أو منتهي الصلاحية', 400);
                }

                $discountAmount = $coupon->calculateDiscount($cart->total);
            }
            //dd($cart);
            // إنشاء الطلب
            $order = Order::create([
                'user_id'           => $user?->id,
                'order_number'      => $this->generateUniqueOrderNumber(),
                'customer_name'     => $request->customer_name ?? $user?->name,
                'customer_phone'    => $request->customer_phone ?? $user?->phone,
                'customer_email'    => $request->customer_email ?? $user?->email,
                'subtotal'          => $cart->subtotal,
                'shipping_amount'   => 0,
                'discount_amount'   => $discountAmount,
                'tax_amount'        => 0,
                'total_amount'      => $cart->total - $discountAmount,
                'payment_method'    => $request->payment_method,
                'status'            => 'pending',
                'notes'             => $request->notes,
                'coupon_id'         => $coupon?->id,
                'image'             => $imagePath, // 👈 هنا
            ]);


            // نقل العناصر من السلة إلى الطلب
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'              => $order->id,
                    'product_id'            => $item->product_id,
                    // 'size_id'               => $item->size_id,
                    // 'color_id'              => $item->color_id,
                    // 'printing_method_id'    => $item->printing_method_id,
                    // 'print_locations'       => $item->print_locations,
                    // 'embroider_locations'   => $item->embroider_locations,
                    // 'selected_options'      => $item->selected_options,
                    // 'design_service_id'     => $item->design_service_id,
                    'quantity'              => $item->quantity,
                    'price_per_unit'        => $item->price_per_unit,
                    'total_price'           => $item->line_total ?? $item->price_per_unit * $item->quantity,
                    'is_sample'             => $item->is_sample,
                    'note'                  => $item->note,
                    'quantity_id'           => $item->quantity_id,
                    // 'image_design'          => $item->image_design,
                ]);
            }

            // تفريغ السلة بعد الطلب
            //  $cart->items()->delete();
            //  $cart->update(['subtotal' => 0, 'total' => 0]);

            if ($request->payment_method === 8) {

                $payment = $this->initiatePaymobPayment($order);

                if (!$payment['success']) {
                    return $this->errorResponse([$payment['message']], 400);
                }

                return $this->successResponse([
                    'payment_url'  => $payment['payment_url'],
                    'shorten_url'  => $payment['shorten_url'],
                    'order_number' => $order->order_number,
                    'message'      => 'جاري توجيهك إلى بوابة الدفع الآمنة...'
                ]);
            }

            return $this->successResponse(
                new OrderDetailsResource($order->load(['items.product'])),
                'تم إنشاء الطلب بنجاح',
                201
            );
        });
    }

    /**
     * إلغاء الطلب (فقط إذا كان pending أو processing)
     */
    public function cancelled($codeOrder)
    {
        $order = Order::where('order_number', $codeOrder)->firstOrFail();

        $user = auth('sanctum')->user();

        // تحقق من الصلاحية
        if ($user && $order->user_id !== $user->id) {
            return $this->errorResponse('غير مصرح لك بإلغاء هذا الطلب', 403);
        }

        if (!$user && !$this->guestCanAccessOrder($order)) {
            return $this->errorResponse('رقم الهاتف مطلوب لإلغاء الطلب', 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return $this->errorResponse('لا يمكن إلغاء الطلب في هذه الحالة', 400);
        }

        $order->update(['status' => 'cancelled']);

        return $this->successResponse(
            new OrderDetailsResource($order),
            'تم إلغاء الطلب بنجاح'
        );
    }

    /**
     * تتبع الطلب برقم الطلب (للمسجلين والزوار)
     */
    public function traceOrder($codeOrder, Request $request)
    {
        $order = Order::with(['items.product'])
            ->where('order_number', $codeOrder)
            ->firstOrFail();

        $user = auth('sanctum')->user();

        // لو مسجل دخول → تأكد إنه صاحب الطلب
        // if ($user && $order->user_id !== $user->id) {
        //     return $this->errorResponse('هذا الطلب ليس لك', 403);
        // }

        // لو زائر → يطلب رقم التليفون
        // if (!$user) {
        //     $phone = $request->input('phone');
        //     if (!$phone || $order->customer_phone !== $phone) {
        //         return $this->errorResponse('رقم الهاتف غير صحيح', 403);
        //     }
        // }

        return $this->successResponse(
            new OrderDetailsResource($order),
            'تم جلب تفاصيل الطلب'
        );
    }

    /**
     * Summary of show
     * @param mixed $orderID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($orderID)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return $this->errorResponse('يجب تسجيل الدخول لعرض تفاصيل الطلب', 401);
        }

        $order = Order::where('id', $orderID)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return $this->successResponse(
            new OrderDetailsResource($order),
            'تم جلب تفاصيل الطلب بنجاح'
        );
    }

    /**
     * تطبيق كوبون خصم على السلة الحالية
     */
    public function applyCoupon(ApplyCouponRequest $request)
    {
        $user = auth()->user();
        $cart = $this->getCurrentCart();

        if ($cart->items()->count() === 0) {
            return $this->errorResponse('السلة فارغة، لا يمكن تطبيق كوبون', 400);
        }

        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

        if (!$coupon || !$coupon->isValidForOrder($cart->total, $user?->id, session()->getId())) {
            return $this->errorResponse('كوبون الخصم غير صالح أو منتهي الصلاحية', 400);
        }

        $discountAmount = $coupon->calculateDiscount($cart->total);

        return $this->successResponse(
            [
                'coupon_id'       => $coupon->id,
                'discount_amount' => $discountAmount,
                'new_total'       => $cart->total - $discountAmount,
            ],
            'تم تطبيق الكوبون بنجاح'
        );
    }

    public function webhook(Request $request)
    {
        return $this->handlePaymobWebhook($request);
    }

    public function paymentMethods(Request $request)
    {


        $isPayment = filter_var(
            $request->input('is_payment'),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        $paymentMethods = $isPayment === false
            ? collect()
            : PaymentMethod::query()
                ->where('is_active', true)
                ->get();

        return $this->successResponse(
            PaymentMethodResource::collection($paymentMethods),
            'تم جلب طرق الدفع بنجاح'
        );
    }


    public function paymentStatus(Request $request)
    {
        $status = $request->query('status'); // success | failed
        $orderId = $request->query('orderId');

        if (!$status || !$orderId) {
            return $this->errorResponse('بيانات غير مكتملة', 400);
        }

        $order = Order::where('order_number', $orderId)
            ->orWhere('id', $orderId)
            ->first();

        if (!$order) {
            return $this->errorResponse('الطلب غير موجود', 404);
        }

        return $this->successResponse([
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'status'       => $status,
            'order_status' => $order->status, // paid / pending / cancelled
            'total'        => $order->total_amount,
        ], 'تم جلب حالة الدفع');
    }

    // ==================== Helpers ====================

    private function getCurrentCart(): Cart
    {
        $user = auth('sanctum')->user();
        $sessionId = request()->header('X-Session-Id') ?: session()->getId();

        return Cart::firstOrCreate(
            $user ? ['user_id' => $user->id] : ['session_id' => $sessionId],
            ['subtotal' => 0, 'total' => 0]
        );
    }

    private function generateUniqueOrderNumber(): string
    {
        do {
            $number = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function guestCanAccessOrder(Order $order): bool
    {
        $phone = request()->input('phone');
        return $phone && $order->customer_phone === $phone;
    }
}
