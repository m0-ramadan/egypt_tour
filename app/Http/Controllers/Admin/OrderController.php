<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\Website\ProductResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\Product;
use App\Models\User;
use App\Services\Like4AppService;
use App\Services\LikeCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $like4AppService;
    protected $likeCardService;

    public function __construct(Like4AppService $like4AppService, LikeCardService $likeCardService)
    {
        $this->like4AppService = $like4AppService;
        $this->likeCardService = $likeCardService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product'])->latest();

        // البحث
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // فلترة حسب طريقة الدفع
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // فلترة حسب المبلغ
        if ($request->filled('amount_from')) {
            $query->where('total_amount', '>=', $request->get('amount_from'));
        }

        if ($request->filled('amount_to')) {
            $query->where('total_amount', '<=', $request->get('amount_to'));
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $orders = $query->paginate(15)->withQueryString();

        // إحصائيات
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
        ];

        return view('Admin.orders.index', compact('orders', 'stats'));
    }


    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $users = User::orderBy('name')->get();

        // جلب المنتجات النشطة فقط مع الترقيم
        $products = Product::where('status_id', 1)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('Admin.orders.create', compact('users', 'products'));
    }


    public function searchProducts(Request $request)
    {
        $search = $request->get('q');

        $products = Product::with(['discount', 'primaryImage'])
            ->where('status_id', 1)
            ->searched($search)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'items' => ProductResource::collection($products->getCollection())->resolve(),
            'next_page_url' => $products->nextPageUrl(),
            'prev_page_url' => $products->previousPageUrl(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
            'per_page' => $products->perPage(),
        ]);
    }
    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $subtotal = 0;
            $hasLike4AppProduct = false;
            $like4AppItems = [];

            foreach ($request->items as $index => $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("المنتج غير موجود: {$item['product_id']}");
                }

                $quantity = (int) $item['quantity'];
                $itemTotal = $quantity * $product->final_price;
                $subtotal += $itemTotal;

                if ($product->external_id) {
                    $hasLike4AppProduct = true;
                }

                if (!$product->external_id && $product->stock !== null) {
                    if ($product->stock < $quantity) {
                        throw new \Exception("الكمية المطلوبة من {$product->name} غير متوفرة. المتاح: {$product->stock}");
                    }
                }
            }

            $discount = $request->discount_amount ?? 0;
            $tax = $request->tax_amount ?? 0;
            $total = $subtotal - $discount + $tax;

            $order = Order::create([
                'user_id' => $request->user_id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone ?? null,
                'status' => $hasLike4AppProduct ? 'processing' : 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'provider' => $hasLike4AppProduct ? 'like4app' : 'internal',
            ]);

            $this->logOrderAction($order, 'create_order', 'internal', [
                'request' => $request->except('_token'),
                'subtotal' => $subtotal,
                'total' => $total,
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $quantity = (int) $item['quantity'];

                // منتجات Like4App: كل وحدة OrderItem مستقل
                if ($product->external_id) {
                    for ($i = 0; $i < $quantity; $i++) {
                        $orderItem = OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => 1,
                            'price_per_unit' => $product->final_price,
                            'total_price' => $product->final_price,
                            'provider_data' => [
                                'external_id' => $product->external_id,
                                'type' => 'like4app',
                                'request_quantity' => 1,
                                'original_requested_quantity' => $quantity,
                            ],
                        ]);

                        $like4AppItems[] = [
                            'product' => $product,
                            'quantity' => 1,
                            'price' => $product->final_price,
                            'order_item_id' => $orderItem->id,
                        ];
                    }
                } else {
                    // المنتجات الداخلية تظل كما هي
                    $itemTotal = $quantity * $product->final_price;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price_per_unit' => $product->final_price,
                        'total_price' => $itemTotal,
                        'provider_data' => null,
                    ]);

                    if ($product->stock !== null) {
                        $product->decrement('stock', $quantity);
                    }
                }
            }

            DB::commit();

            if ($hasLike4AppProduct) {
                try {
                    $this->processLike4AppOrder($order, $like4AppItems);

                    $order->update([
                        'status' => 'completed',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Like4App processing failed after local order creation', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    $order->update([
                        'status' => 'processing',
                        'provider_response' => array_merge(
                            is_array($order->provider_response) ? $order->provider_response : [],
                            ['error' => $e->getMessage()]
                        ),
                    ]);

                    return redirect()->route('admin.orders.show', $order->id)
                        ->with('warning', 'تم إنشاء الطلب محليًا، لكن حدثت مشكلة أثناء تنفيذ طلب لايك كارد: ' . $e->getMessage());
                }
            }

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'تم إنشاء الطلب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الطلب: ' . $e->getMessage());
        }
    }
    /**
     * Process Like4App order
     */

    protected function processLike4AppOrder($order, $like4AppItems)
    {
        $allSerials = [];
        $providerResponses = [];

        foreach ($like4AppItems as $item) {
            $product = $item['product'];
            $quantity = 1;

            $this->logOrderAction($order, 'like4app_request', 'like4app', [
                'product_id' => $product->id,
                'order_item_id' => $item['order_item_id'],
                'external_id' => $product->external_id,
                'quantity' => $quantity,
            ]);

            $response = $this->like4AppService->createOrder(
                $product->external_id,
                1,
                (string) ('ORDER_' . $order->id . '_' . $item['order_item_id'] . '_' . time())
            );

            $providerResponses[] = [
                'product_id' => $product->id,
                'order_item_id' => $item['order_item_id'],
                'product_name' => $product->name,
                'request' => [
                    'external_id' => $product->external_id,
                    'quantity' => 1,
                ],
                'response' => $response,
            ];

            $this->logOrderAction($order, 'like4app_response', 'like4app', [
                'product_id' => $product->id,
                'order_item_id' => $item['order_item_id'],
                'external_id' => $product->external_id,
                'response' => $response,
            ]);

            $data = $response['data'] ?? null;
            $isSuccess = ($response['success'] ?? false) && is_array($data) && (($data['response'] ?? 0) == 1);

            if ($isSuccess) {
                $serials = $this->like4AppService->parseSerialCodes($data);

                $orderItem = $order->items()->find($item['order_item_id']);

                if ($orderItem) {
                    $existingProviderData = $orderItem->provider_data;

                    if (is_string($existingProviderData)) {
                        $decoded = json_decode($existingProviderData, true);
                        $existingProviderData = is_array($decoded) ? $decoded : [];
                    }

                    if (!is_array($existingProviderData)) {
                        $existingProviderData = [];
                    }

                    $orderItem->update([
                        'serial_codes' => $serials,
                        'provider_data' => array_merge($existingProviderData, [
                            'like4app_response' => $data,
                            'order_id' => $data['orderId'] ?? null,
                        ]),
                    ]);
                }

                $allSerials = array_merge($allSerials, $serials);

                if (!empty($data['orderId']) && !$order->provider_order_id) {
                    $order->update([
                        'provider_order_id' => $data['orderId'],
                        'provider_response' => $providerResponses,
                        'serial_codes' => $allSerials,
                    ]);
                }
            } else {
                $errorMsg =
                    $response['error']
                    ?? ($data['message'] ?? null)
                    ?? ($data['errorCode'] ?? null)
                    ?? 'فشل الاتصال بخدمة لايك كارد';

                $this->logOrderAction($order, 'error', 'like4app', [
                    'product_id' => $product->id,
                    'order_item_id' => $item['order_item_id'],
                    'error' => $errorMsg,
                    'response' => $response,
                ]);

                throw new \Exception("فشل في إنشاء طلب لايك كارد للمنتج {$product->name}: {$errorMsg}");
            }
        }

        $order->update([
            'provider_response' => $providerResponses,
            'serial_codes' => $allSerials,
        ]);

        return true;
    }
    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = Order::with([
            'user',
            'items.product',
            'logs' => function ($q) {
                $q->latest();
            }
        ])->findOrFail($id);

        foreach ($order->items as $item) {
            if (!empty($item->serial_codes) && is_array($item->serial_codes)) {
                $serials = $item->serial_codes;

                foreach ($serials as &$serial) {
                    if (is_array($serial) && !empty($serial['serial_code'])) {
                        $serial['voucher_code'] = $this->likeCardService->decryptSerial($serial['serial_code']);
                    }
                }

                $item->serial_codes = $serials;
            }
        }

        return view('Admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit($id)
    {
        $order = Order::with('items')->findOrFail($id);
        $users = User::orderBy('name')->get();
        $products = Product::where('status_id', 1)->get();

        return view('Admin.orders.edit', compact('order', 'users', 'products'));
    }


    /**
     * Update the specified order
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,delivered,cancelled',
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,wallet',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $order->update([
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            $this->logOrderAction($order, 'update_order', $order->provider, [
                'status' => $request->status,
                'payment_method' => $request->payment_method
            ]);

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'تم تحديث الطلب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الطلب: ' . $e->getMessage());
        }
    }

    /**
     * Log order action
     */
    protected function logOrderAction($order, $action, $provider = null, $data = [])
    {
        OrderLog::create([
            'order_id' => $order->id,
            'action' => $action,
            'provider' => $provider,
            'request_data' => $data,
            'response_data' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Get product details for AJAX
     */
    public function getProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'final_price' => $product->final_price,
            'stock' => $product->stock,
            'external_id' => $product->external_id,
            'image' => $product->image ? asset('storage/' . $product->image) : asset('images/default-product.png'),
            'provider' => $product->external_id ? 'like4app' : 'internal'
        ]);
    }

    /**
     * Search products for AJAX
     */
    // public function searchProducts(Request $request)
    // {
    //     $search = $request->get('q');

    //     $products = Product::where('status_id', 1)
    //         ->where(function ($query) use ($search) {
    //             $query->where('name', 'LIKE', "%{$search}%")
    //                 ->orWhere('sku', 'LIKE', "%{$search}%")
    //                 ->orWhere('external_id', 'LIKE', "%{$search}%");
    //         })
    //         ->limit(10)
    //         ->get()
    //         ->map(function ($product) {
    //             return [
    //                 'id' => $product->id,
    //                 'name' => $product->name,
    //                 'price' => $product->final_price,
    //                 'stock' => $product->stock,
    //                 'external_id' => $product->external_id,
    //                 'provider' => $product->external_id ? 'like4app' : 'internal',
    //                 'image' => $product->image ? asset('storage/' . $product->image) : asset('images/default-product.png'),
    //             ];
    //         });

    //     return response()->json($products);
    // }
    public function destroy(Order $order)
    {
        // إعادة المخزون في حالة الحذف
        if (in_array($order->status, ['pending', 'processing'])) {
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        }

        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'تم حذف الطلب بنجاح.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // تحديث الحالة
        $order->status = $newStatus;

        // تحديث التواريخ
        if ($newStatus == 'shipped' && $oldStatus != 'shipped') {
            $order->shipped_at = now();
        }

        if ($newStatus == 'delivered' && $oldStatus != 'delivered') {
            $order->delivered_at = now();
        }

        // إذا تم إلغاء الطلب، إعادة المخزون
        if ($newStatus == 'cancelled' && !in_array($oldStatus, ['cancelled', 'delivered'])) {
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        }

        $order->save();

        // إضافة ملاحظة إذا وجدت
        if ($request->filled('notes')) {
            // يمكنك إضافة سجل للملاحظات هنا
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'status' => $order->status,
            'status_label' => $order->status_label,
        ]);
    }

    public function print(Order $order)
    {
        $order->load(['user', 'items.product']);

        return view('Admin.orders.print', compact('order'));
    }

    public function export(Request $request)
    {
        // يمكنك إضافة تصدير الطلبات بصيغة Excel أو PDF هنا
        return response()->json(['message' => 'سيتم إضافة التصدير لاحقاً']);
    }

    public function statistics()
    {


        // إحصائيات متقدمة
        $stats = [
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
            'average_order_value' => Order::where('status', '!=', 'cancelled')->avg('total_amount') ?? 0,

            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount'),

            'weekly_orders' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'weekly_revenue' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('status', '!=', 'cancelled')->sum('total_amount'),

            'monthly_orders' => Order::whereMonth('created_at', now()->month)->count(),
            'monthly_revenue' => Order::whereMonth('created_at', now()->month)
                ->where('status', '!=', 'cancelled')->sum('total_amount'),

            'status_counts' => Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),

            'top_products' => DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
                ->groupBy('order_items.product_id', 'products.name')
                ->orderBy('total_quantity', 'desc')
                ->limit(10)
                ->get(),
        ];
        return view('Admin.orders.statistics', compact('stats'));
    }
}
