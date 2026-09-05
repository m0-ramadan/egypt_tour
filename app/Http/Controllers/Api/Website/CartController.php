<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Material;
use Illuminate\Http\Request;
use App\Models\DesignService;
use App\Models\PrintLocation;
use App\Models\PrintingMethod;
use App\Models\ProductOptions;
use App\Traits\ApiResponseTrait;
use App\Models\EmbroiderLocation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\CartResource;
use App\Http\Requests\Website\AddToCartRequest;
use App\Http\Resources\Website\CartItemResource;
use App\Http\Requests\Website\UpdateCartItemRequest;

class CartController extends Controller
{
    use ApiResponseTrait;

    // جلب السلة الحالية أو إنشاء واحدة جديدة (مستخدم مسجل أو session visitor)
    private function getCurrentCart(): Cart
    {
        $user = auth()->user();
        $sessionId = request()->header('X-Session-Id') ?: session()->getId();

        return Cart::firstOrCreate(
            $user ? ['user_id' => $user->id] : ['session_id' => $sessionId],
            ['subtotal' => 0, 'total' => 0]
        );
    }

    // عرض السلة
    public function index(Request $request)
    {
        $cart = $this->getCurrentCart();
        return $this->success(new CartResource($cart), 'تم جلب السلة بنجاح');
    }

    // إضافة منتج للسلة
    public function add(AddToCartRequest $request)
    {
        $cart = $this->getCurrentCart();

        return DB::transaction(function () use ($request, $cart) {
            // حساب السعر بناء على الطلب
            $priceData = $this->calculateItemPrice($request);

            // تجهيز الـ JSON fields واستحداث hash_key لمنع التكرار
          //  $printLocationsJson = $request->filled('print_locations') ? json_encode($request->print_locations, JSON_UNESCAPED_UNICODE) : null;
         //   $embroiderLocationsJson = $request->filled('embroider_locations') ? json_encode($request->embroider_locations, JSON_UNESCAPED_UNICODE) : null;
          //  $selectedOptionsJson = $request->filled('selected_options') ? json_encode($request->selected_options, JSON_UNESCAPED_UNICODE) : null;

            $hashAttributes = [
                'product_id' => (int) $request->product_id,
              //  'size_id' => $request->size_id ? (int) $request->size_id : null,
                //'color_id' => $request->color_id ? (int) $request->color_id : null,
               // 'material_id' => $request->material_id ? (int) $request->material_id : null,
              //  'printing_method_id' => $request->printing_method_id ? (int) $request->printing_method_id : null,
              //  'print_locations' => $request->print_locations ?? [],
              //  'embroider_locations' => $request->embroider_locations ?? [],
                //'selected_options' => $request->selected_options ?? [],
                'design_service_id' => $request->design_service_id ?? null,
                'is_sample' => (bool) $request->boolean('is_sample', false),
            ];
            $hashKey = md5(json_encode($hashAttributes, JSON_UNESCAPED_UNICODE));

            // حاول الحصول على العنصر بنفس hash_key
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('hash_key', $hashKey)
                ->first();

            $printingMethodId = null;
            if ($request->filled('printing_method_id')) {
                $valid = Product::whereKey($request->product_id)
                    ->whereHas('printingMethods', function ($q) use ($request) {
                        $q->where('printing_methods.id', $request->printing_method_id);
                    })
                    ->exists();
                if ($valid) {
                    $printingMethodId = (int) $request->printing_method_id;
                }
            }

            if ($cartItem) {
                // لو العنصر موجود — نزود الكمية بالكمية الواردة
                $cartItem->increment('quantity', $request->quantity);
                $cartItem->price_per_unit = $priceData['price_per_unit'];
                $cartItem->line_total = $cartItem->quantity * $cartItem->price_per_unit;
                $cartItem->save();
            } else {
                // انشئ عنصر جديد
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,

                    'note' => $request->input('note'),
                    'quantity_id' => $request->input('quantity_id'),
                    'quantity' => $request->quantity,
                    'price_per_unit' => $priceData['price_per_unit'],
                    'line_total' => $priceData['line_total'],
                    'is_sample' => $request->boolean('is_sample', false),
                    'hash_key' => $hashKey,
                ]);
            }

            $this->recalculateCart($cart);

            return $this->success(new CartItemResource($cartItem), 'تمت الإضافة إلى السلة');
        });
    }

    // تحديث عنصر في السلة
    public function update(CartItem $cartItem, UpdateCartItemRequest $request)
    {
        $this->authorizeCartItem($cartItem);

        return DB::transaction(function () use ($cartItem, $request) {
            // نحضر البيانات الجديدة مع الاحتفاظ بالقيم القديمة إن لم تُقدم
            $data = $request->only([
                'quantity',
                'is_sample',
                'note',
                'quantity_id',
                'image_design'
            ]);

            // نحسب السعر بناء على التخصيص الجديد — نمرر المنتج الحالي كـ param اختياري
            $priceRequest = new Request(array_merge($request->all(), [
                'product_id' => $cartItem->product_id,
                'quantity' => $data['quantity'] ?? $cartItem->quantity,
            ]));

            $priceData = $this->calculateItemPrice($priceRequest, $cartItem->product);


            // إعادة توليد hash_key لأن التخصيصات قد تتغير
            $hashAttributes = [
                'product_id' => $cartItem->product_id,
                'is_sample' => isset($data['is_sample']) ? (bool)$data['is_sample'] : (bool)$cartItem->is_sample,
            ];
            $newHashKey = md5(json_encode($hashAttributes, JSON_UNESCAPED_UNICODE));

            // قم بالتحديث
            $cartItem->update([
                'quantity' => $data['quantity'] ?? $cartItem->quantity,
                'note' => $data['note'] ?? $cartItem->note,
                'quantity_id' => $data['quantity_id'] ?? $cartItem->quantity_id,
                'is_sample' => isset($data['is_sample']) ? (bool)$data['is_sample'] : $cartItem->is_sample,
                'price_per_unit' => $priceData['price_per_unit'],
                'line_total' => $priceData['line_total'],
                'hash_key' => $newHashKey,
            ]);

            // إعادة حساب السلة
            $this->recalculateCart($cartItem->cart);

            return $this->success(new CartResource($cartItem->cart->fresh(['items.product', 'items.size', 'items.color', 'items.printingMethod', 'items.designService'])), 'تم تحديث العنصر بنجاح');
        });
    }

    // حذف عنصر من السلة
    public function remove(CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        return DB::transaction(function () use ($cartItem) {
            $cart = $cartItem->cart;
            $cartItem->delete();
            $this->recalculateCart($cart);
            return $this->success(new CartResource($cart->fresh(['items'])), 'تم حذف العنصر من السلة');
        });
    }

    // تفريغ السلة
    public function clear()
    {
        $cart = $this->getCurrentCart();
        $cart->items()->delete();
        $cart->update(['subtotal' => 0, 'total' => 0]);
        return $this->success(null, 'تم تفريغ السلة');
    }

    // رفع صورة للعنصر في السلة
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $cartItem = CartItem::findOrFail($request->input('cart_item_id'));
        $this->authorizeCartItem($cartItem);

        return DB::transaction(function () use ($request, $cartItem) {
            $imagePath = $request->file('image')->store('cart_items', 'public');
            $cartItem->update(['image_design' => $imagePath]);

            return $this->success(new CartItemResource($cartItem), 'تم رفع الصورة بنجاح');
        });
    }

    // --- Helpers ---

    private function authorizeCartItem(CartItem $cartItem)
    {
        $currentCart = $this->getCurrentCart();
        if ($cartItem->cart_id !== $currentCart->id) {
            abort(403, 'هذا العنصر لا يخص سلتك');
        }
    }

    /**
     * حساب السعر لعُنصر السلة.
     * يقبل Request و product اختياري (مفيد عند التحديث).
     *
     * @param Request $request
     * @param Product|null $product
     * @return array ['price_per_unit' => float, 'line_total' => float]
     */


 
    private function calculateItemPrice(Request $request, Product $product = null): array
    {
        $product  = $product ?? Product::findOrFail($request->product_id);
        $quantity = (int) ($request->quantity ?? 1);

        // ============================
        // 🟢 base unit price
        // ============================
        $unitPrice = $product->base_price ?? 0;


        // ============================
        // 🧮 totals
        // ============================
        $lineTotal = ($unitPrice * $quantity) ;

        return [
            'price_per_unit' => round($unitPrice,  2),
            'line_total'     => round($lineTotal, 2),
        ];
    }

    private function recalculateCart(Cart $cart)
    {
        $subtotal = $cart->items()->sum('line_total');
        $cart->update([
            'subtotal' => $subtotal,
            'total' => $subtotal, // مستقبلًا: + شحن - خصم
        ]);
    }
}
