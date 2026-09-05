<?php

namespace App\Http\Controllers\Admin;

use Log;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Trip;
use App\Models\Region;
use App\Models\Branchs;
use App\Models\Shipment;
use App\Models\Warehouse;
use App\Models\Settlement;
use App\Traits\qr_codeTrait;

use Illuminate\Http\Request;
use App\Traits\PTHelperTrait;
use App\Models\Representative;
use App\Models\ShipmentContent;
use App\Traits\SyncClientToErp;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\TranslatableTrait;
use App\Models\ShipmentClientTrip;
use Illuminate\Support\Facades\DB;
use App\Models\TripShipmentContent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use App\Traits\FirebaseNotificationTrait;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TripsController extends Controller
{

    use TranslatableTrait, FirebaseNotificationTrait, PTHelperTrait, qr_codeTrait, SyncClientToErp;
    //     public function __construct()
    // {
    //     $this->middleware('auth:admin');
    //     $this->middleware('permission:عرض رحلات المندوب', ['only' => ['index', 'driver']]);
    //     $this->middleware('permission:إضافة رحلة', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:تعديل رحلة', ['only' => ['edit', 'editDriver', 'update']]);
    //     $this->middleware('permission:عرض تفاصيل الرحلة', ['only' => ['show']]);
    //     $this->middleware('permission:حذف رحلة', ['only' => ['destroy']]);
    //     $this->middleware('permission:طباعة رحلة', ['only' => ['printPdf']]);
    //     $this->middleware('permission:عرض رمز QR', ['only' => ['qrCode']]);
    //     $this->middleware('permission:تغيير حالة الرحلة', ['only' => ['changeStatus']]);
    // }

    public function index(Request $request)
    {
        $query = Trip::where('type_driver', 1)
            ->with(['representative', 'shipments'])
            ->withCount('shipments')
            ->orderBy('created_at', 'desc');

        // إضافة البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('representative', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shipments', function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%");
                    });
            });
        }

        if (auth()->user()->role) {
            $query->where('branches_from', auth()->user()->branch_id);
        }

        $trips = $query->paginate(10); // عدد العناصر لكل صفحة

        foreach ($trips as $trip) {
            $trip->updateTripStatus($trip);
        }
        $t = 1;

        return view('Admin.trips.index', compact('trips', 't'));
    }


    public function driver(Request $request)
    {
        $query = Trip::where('type_driver', 0)
            ->with(['representative', 'shipments'])
            ->withCount('shipments')
            ->orderBy('created_at', 'desc');

        // إضافة البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('representative', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shipments', function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%");
                    });
            });
        }

        // تنفيذ الاستعلام مع التصفح
        $trips = $query->paginate(10); // عدد العناصر لكل صفحة

        foreach ($trips as $trip) {
            $trip->updateTripStatus($trip);
        }
        $t = 0;
        return view('Admin.trips.index', compact('trips', 't'));
    }
    public function archive(Request $request)
    {
        try {
            $query = Trip::onlyTrashed()
                ->with(['representative', 'shipments'])
                ->withCount('shipments')
                ->orderBy('deleted_at', 'desc');

            // البحث في الكود أو اسم المندوب أو كود الشحنة
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhereHas('representative', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('shipments', function ($q) use ($search) {
                            $q->where('code', 'like', "%{$search}%");
                        });
                });
            }

            // تنفيذ الاستعلام مع التصفح
            $trips = $query->paginate(10);

            return view('Admin.trips.archive', compact('trips'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء استرجاع الأرشيف: ' . $e->getMessage());
        }
    }

    public function representativeTransfer(Request $request)
    {
        $query = Trip::where('transit', 1)->orderBy('created_at', 'desc');

        if (auth()->user()->role) {
            $query->where('branches_from', auth()->user()->branch_id);
        }

        $trips = $query->get();

        return view('Admin.trips.trip_transit', compact('trips'));
    }

    /**
     * Summary of outgoing
     * @param \Illuminate\Http\Request $request
     */
    public function outgoing()
    {
        $admin = auth()->user();
        $trips = Trip::where('type_driver', 0)->with(['representative', 'shipments'])->where('branches_from', $admin->branch_id)
            ->withCount('shipments')->orderBy('created_at', 'desc')->paginate(10);
        foreach ($trips as $trip) {
            $trip->updateTripStatus($trip);
        }
        return view('Admin.trips.index', compact('trips'));
    }

    /**
     * Summary of ingoing
     * @param \Illuminate\Http\Request $request
     */
    public function ingoing()
    {
        $admin = auth()->user();
        // where('status', '>=', 2)->
        $trips = Trip::whereNot('status', 4)->with(['representative', 'shipments'])->where('branches_to', $admin->branch_id)
            ->withCount('shipments')->orderBy('created_at', 'desc')->get();
        // تحديث حالة كل رحلة بناءً على الشحنات
        foreach ($trips as $trip) {
            $trip->updateTripStatus($trip);
        }
        return view('Admin.trips.index', compact('trips'));
    }

    public function createTrip(Request $request)
    {

        if ($request->t == 1) {
            $representatives = Representative::where('type', 1)->where('status', true)->get();
            if (auth()->user()->role != 0) {
                $shipments = Shipment::where('type', 2)
                    ->where('branches_from', auth()->user()?->branch_id)
                    ->where('active', 1)
                    //  ->whereNotIn('status_id', [5, 8])
                    ->whereDoesntHave('trips')
                    ->orderByDesc('created_at')
                    ->get();
            } else {
                $shipments = Shipment::where('type', 2)
                    ->where('active', 1)
                    //  ->whereNotIn('status_id', [5, 8])
                    ->whereDoesntHave('trips')
                    ->orderByDesc('created_at')
                    ->get();
            }
        } else {

            $representatives = Representative::where('type', 0)->where('status', true)->get();
            if (auth()->user()->role == 0) {

                $shipments = Shipment::with(['contents', 'person'])
                    ->where('type', $request->t == 1 ? 2 : 1)
                    ->where('active', 1)
                    ->where('branches_from', auth()->user()?->branch_id)
                    ->whereNotIn('status_id', [5, 8])
                    ->whereHas('contents', function ($query) {
                        $query->where('quantity', '>', 0)->where('remaining', '>', 0);
                    })
                    ->orderByDesc('created_at')
                    ->get();
            } else {

                $shipments = Shipment::with(['contents', 'person'])
                    ->where('type', $request->t == 1 ? 2 : 1)
                    ->where('active', 1)
                    ->where('branches_from', auth()->user()?->branch_id)
                    ->whereNotIn('status_id', [5, 8])
                    ->whereHas('contents', function ($query) {
                        $query->where('quantity', '>', 0)->where('remaining', '>', 0);
                    })
                    ->orderByDesc('created_at')
                    ->get();
            }
        }


        // $shipments = Shipment::with(['contents', 'person'])
        //     ->where('type', $request->t == 1 ? 2 : 1)
        //     ->where('active', 1)
        //     ->whereNotIn('status_id', [5, 8])
        //     ->whereDoesntHave('trips')
        //     ->has('contents')
        //     ->orderByDesc('created_at')
        //     ->get();

        $user = auth()->user();

        $query = ShipmentContent::query()
            ->whereIn('shipment_id', $shipments->pluck('id'))
            ->orderByDesc('created_at');

        if ($user->branch_id) {
            $query->whereHas('shipment', function ($q) use ($user) {
                $q->where('branches_from', $user->branch_id);
            });
        }

        $contents = $query->get();


        $branches = Branchs::get();
        $regions = Region::get();

        if ($shipments->isEmpty()) {
            session()->flash('info', 'لا توجد شحنات متاحة للإضافة إلى رحلة جديدة');
        }

        $view = $request->t == 1 ? 'Admin.trips.create' : 'Admin.trips.create-driver';
        return view($view, compact('representatives', 'shipments', 'branches', 'regions', 'contents'));
    }
    public function createTripTransfer(Request $request)
    {
        $representatives = Representative::where('type', 0)->where('status', true)->get();

        $contents = TripShipmentContent::where('status_id', 10)->orderByDesc('created_at');

        if (auth()->user()->branch_id) {
            $contents->whereHas('shipmentContent.shipment', function ($query) {
                $query->where('branches_from', auth()->user()->branch_id);
            });
        }

        $contents = $contents->get();

        $branches = Branchs::get();
        $regions = Region::get();

        return view('Admin.trips.create_trip_transit', compact('representatives', 'branches', 'regions', 'contents'));
    }

    public function storeDriver(Request $request)
    {
        try {
            $validated = $request->validate([
                'representative_id' => 'nullable|exists:representatives,id',
                'contents' => 'required|array|min:1', // Changed to required with at least one entry
                'contents.*.id' => 'required|exists:shipment_contents,id', // Changed to required
                'contents.*.quantity' => 'required|integer|min:1', // Changed to required
                'branch_from' => 'nullable|exists:branchss,id',
                'branch_to' => 'nullable|exists:branchss,id',
                'city_id' => 'nullable|exists:cities,id',
                'expense_value' => 'nullable|numeric|min:0',
                'refund_value' => 'nullable|numeric|min:0',
                'value_drive' => 'nullable|numeric|min:0',
                'type_coin' => 'nullable',
                'notes' => 'nullable|string|max:255',
            ], [
                'contents.required' => 'يجب إضافة محتوى واحد على الأقل للرحلة.',
                'contents.min' => 'يجب إضافة محتوى واحد على الأقل للرحلة.',
                'contents.*.id.required' => 'معرف المحتوى مطلوب.',
                'contents.*.quantity.required' => 'كمية المحتوى مطلوبة.',
            ]);

            DB::beginTransaction();

            // Calculate total shipping cost from related shipments
            $contentIds = array_column($validated['contents'] ?? [], 'id');
            $contents = ShipmentContent::whereIn('id', $contentIds)->with('shipment')->get();
            $shipmentIds = $contents->pluck('shipment_id')->unique()->toArray();
            $shipments = Shipment::whereIn('id', $shipmentIds)->get();
            // $totalShippingCost = $shipments->sum('shipping_cost');
            $refund_value = ($validated['value_drive'] ?? 0) - ($validated['expense_value'] ?? 0);

            // // Generate trip code
            // $branch_from = Branchs::findOrFail($validated['branch_from']);
            // $prefix = strtoupper(substr($branch_from->key, 0, 2));
            // $branch_to = Branchs::findOrFail($validated['branch_to']);
            // $prefix .= strtoupper(substr($branch_to->key, 0, 2));
            // $tripCount = Trip::where('branches_from', $validated['branch_from'])->count() + 1;
            // $code = $prefix . $tripCount;
            $branchFrom = Branchs::findOrFail($validated['branch_from']);
            $branchTo   = Branchs::findOrFail($validated['branch_to']);

            // الحروف الأولى من الفروع
            $prefix = strtoupper(substr($branchFrom->key, 0, 2)) . strtoupper(substr($branchTo->key, 0, 2));

            // $lastTripNumber = Trip::where('branches_from', $branchFrom->id)->max('sequence_number');
            // $nextTripNumber = $lastTripNumber ? $lastTripNumber + 1 : 1;
            // $code = $prefix . $nextTripNumber;
            $firstLetterFrom = strtoupper(substr($branchFrom->key, 0, 2));
            $firstLetterTo   = strtoupper(substr($branchTo->key, 0, 2));

            // Count all trips that started from this branch
            $tripCount = Trip::withTrashed()->where('branches_from', $branchFrom->id)->count() + 1;


            do {
                // Example: CAMD1
                $code = $firstLetterFrom . $firstLetterTo . $tripCount;

                $regex = '^' . preg_quote($firstLetterFrom, '/') . '[A-Z0-9]*' . $tripCount . '$';
                $exists = Trip::where('code', 'REGEXP', $regex)->exists();

                if ($exists) {
                    $tripCount++;
                }
            } while ($exists);

            // Get representative for currency
            $representative = Representative::findOrFail($validated['representative_id']);

            // Create trip
            $trip = Trip::create([
                'name' => $validated['notes'] ?? '',
                'status' => 1,
                'representative_id' => $validated['representative_id'],
                'type_coin' => $validated['type_coin'] ?? null,
                'code' => $code,
                'branches_from' => $validated['branch_from'],
                'branches_to' => $validated['branch_to'],
                'city_id' => $validated['city_id'] ?? null,
                'type_driver' => 0, // Driver
                'expense_value' => $validated['expense_value'] ?? 0,
                'refund_value' => $refund_value,
                'value_drive' => $validated['value_drive'] ?? 0,
                'admin_id' => auth()->user()->id,

                'qr_code' => null,
            ]);

            $this->createJournal($trip, $validated['contents']);
            // Generate and save QR code
            $qrCodePath = 'qr_codes/trip_' . $trip->id . '.png';
            $qrCodeContent = $trip->code;
            //QrCode::format('png')->size(300)->generate($qrCodeContent, storage_path('app/public/' . $qrCodePath));
            $trip->update(['qr_code' => $qrCodePath]);

            // Attach contents to trip and update shipments

            foreach ($validated['contents'] ?? [] as $contentData) {
                $content = ShipmentContent::findOrFail($contentData['id']);
                if ($contentData['quantity'] > $content->remaining) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "الكمية المختارة للمحتوى {$content->name} تتجاوز الكمية المتاحة ({$content->remaining})")->withInput();
                }
                $trip->contents()->attach($content->id, ['quantity' => $contentData['quantity']]);
                $content->taken += $contentData['quantity'];
                $newRemaining = $content->remaining - $contentData['quantity'];
                $content->remaining = max(0, $newRemaining);
                $content->save();
                // Update related shipment
                $shipment = $content->shipment;
                $shipment->status_id = 1;
                $shipment->save();
                // $this->createShipment($shipment, $validated['representative_id'], $validated['branch_from']);
                $this->assignDeliveryToShipment($trip, $shipment->id);
                $warContent = Warehouse::where('trip_id', 0)->where('shipment_content_id', $contentData['id'])->first();
                $warContent->update([
                    'quantity' => $warContent->quantity - $contentData['quantity']
                ]);
                $warContent->update([
                    'finnished' => $warContent->quantity ? 0 : 1
                ]);
                //حالة الكميه الجديده 
                Warehouse::create(['branches_from' => $warContent->branches_from, 'trip_id' => $trip->id, 'shipment_id' => $shipment->id, 'shipment_content_id' => $contentData['id'], 'status' => 2, 'trip_content_id' =>  TripShipmentContent::orderByDesc('id')->firstOrFail()->id, 'quantity' => $contentData['quantity'], 'finnished' => 0]);

                // Notify client
                $client = $shipment->client;
                if ($client) {
                    $deviceTokens = DB::table('device_tokens')
                        ->where('model_type', get_class($client))
                        ->where('model_id', $client->id)
                        ->pluck('device_token')
                        ->toArray();

                    foreach ($deviceTokens as $deviceToken) {
                        $notificationRequest = new Request([
                            'device_token' => $deviceToken,
                            'title' => 'تأكيد الشحنة',
                            'body' => "تم إضافة شحنتك {$shipment->code} إلى رحلة {$trip->code} بنجاح",
                            'icon' => '',
                        ]);
                        $response = $this->sendFirebaseNotification($notificationRequest);
                        if ($response->getStatusCode() !== 200) {
                            Log::error('Failed to send notification for shipment confirmation', [
                                'shipment_id' => $shipment->id,
                                'client_id' => $client->id,
                                'device_token' => $deviceToken,
                                'error' => $response->getData()->error ?? 'Unknown error',
                            ]);
                        }
                    }
                } else {
                    Log::warning('No client associated with shipment for notification', [
                        'shipment_id' => $shipment->id,
                    ]);
                }
            }

            // Attach shipments to trip
            $trip->shipments()->attach($shipmentIds);

            // Notify representative
            $notificationSent = false;
            if ($trip->representative) {
                $deviceTokens = $trip->representative->deviceToken()->pluck('device_token')->toArray();
                foreach ($deviceTokens as $deviceToken) {
                    $notificationRequest = new Request([
                        'device_token' => $deviceToken,
                        'title' => 'تمت إضافتك إلى رحلة',
                        'body' => "تمت إضافتك إلى رحلة جديدة بكود {$trip->code}.",
                        'icon' => '',
                    ]);
                    $response = $this->sendFirebaseNotification($notificationRequest);
                    if ($response->getStatusCode() === 200) {
                        $notificationSent = true;
                    } else {
                        Log::error('Failed to send notification to representative', [
                            'trip_id' => $trip->id,
                            'representative_id' => $validated['representative_id'],
                            'device_token' => $deviceToken,
                            'error' => $response->getData()->error ?? 'Unknown error',
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.driver')->with('success', 'تم إنشاء رحلة السائق بنجاح' . ($notificationSent ? ' وتم إرسال الإشعار' : '') . ' مع رمز QR');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating driver trip: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }

    public function storeRepresentative(Request $request)
    {

        try {
            $validated = $request->validate([
                'representative_id' => 'nullable|exists:representatives,id',
                'type_coin' => 'nullable',
                'shipments' => 'nullable',
                'shipments.*' => 'exists:shipments,id',
                'branch_from' => 'nullable',
                'branch_to' => 'nullable',
                'city_id' => 'nullable|exists:regions,id',
                'notes' => 'nullable|string|max:255',
            ]);

            // $branch_from = Branchs::findOrFail($validated['branch_from']);
            // $prefix = strtoupper(substr($branch_from->key, 0, 2));
            // if ($validated['city_id']) {
            //     $city_id = Region::findOrFail($validated['city_id']);
            //     $prefix .= strtoupper(substr($city_id->key, 0, 2));
            // } else {
            //     return redirect()->back()->with('error', ' يجب ان تختار المدينه المتوجه اليه ');
            // }

            // $tripCount = Trip::count() + 1;
            // $code = $prefix . $tripCount;
            $branch_from = Branchs::findOrFail($validated['branch_from']);
            $prefix = strtoupper(substr($branch_from->key, 0, 9));

            if ($validated['city_id']) {
                $city = Region::findOrFail($validated['city_id']);
                $prefix .= strtoupper(substr($city->key, 0, 9));
            } else {
                return redirect()->back()->with('error', 'يجب أن تختار المدينة المتوجه إليها');
            }

            // جرب لحد ما تلاقي كود مش مستخدم
            $tripCount = Trip::count() + 1;
            $code = $prefix . $tripCount;

            while (Trip::where('code', $code)->exists()) {
                $tripCount++;
                $code = $prefix . $tripCount;
            }

            if (Trip::where('code', $code)->exists()) {
                return redirect()->back()->with('error', 'الكود موجود بالفعل في جدول الرحلات');
            }

            $representative = Representative::find($validated['representative_id']);

            DB::beginTransaction();

            $trip = Trip::create([
                'name' => $validated['notes'], // Use notes as name
                'status' => 1,
                'representative_id' => $validated['representative_id'],
                'type_coin' =>  $validated['type_coin'],
                'code' => $code,
                'branches_from' => $validated['branch_from'],
                // 'branches_to' => $validated['branch_to'] ?? null,
                'city_id' => $validated['city_id'],
                'type_driver' => 1, // Representative
                'qr_code' => null,
                'admin_id' => auth()->user()->id,

            ]);

            $qrCodePath = 'qr_codes/trip_' . $trip->id . '.png';
            $qrCodeContent = $trip->code;
            // QrCode::format('png')->size(300)->generate($qrCodeContent, storage_path('app/public/' . $qrCodePath));
            $trip->update(['qr_code' => $qrCodePath]);

            if (!empty($validated['shipments'])) {
                $trip->shipments()->attach($validated['shipments']);
                foreach ($trip->shipments as $shipment) {
                    $shipment->status_id = 1;
                    $shipment->representative_id = $validated['representative_id'];

                    $shipment->save();

                    $result = $this->createShipmentForTRep($shipment, $validated['representative_id'], $validated['branch_from']);


                    $client = $shipment->client;
                    if ($client) {
                        $deviceTokens = DB::table('device_tokens')
                            ->where('model_type', get_class($client))
                            ->where('model_id', $client->id)
                            ->pluck('device_token')
                            ->toArray();

                        foreach ($deviceTokens as $deviceToken) {
                            $notificationRequest = new Request([
                                'device_token' => $deviceToken,
                                'title' => 'تأكيد الشحنة',
                                'body' => "تم اضافة شحنتك  {$shipment->code} الي رحله{$trip->code}  بنجاح",
                                'icon' => '',
                            ]);
                            $response = $this->sendFirebaseNotification($notificationRequest);
                            if ($response->getStatusCode() === 200) {
                                $notificationSent = true;
                            } else {
                                Log::error('Failed to send notification for shipment confirmation', [
                                    'shipment_id' => $shipment->id,
                                    'client_id' => $client->id,
                                    'device_token' => $deviceToken,
                                    'error' => $response->getData()->error ?? 'Unknown error',
                                ]);
                            }
                        }
                    } else {
                        Log::warning('No client associated with shipment for notification', [
                            'shipment_id' => $shipment->id,
                        ]);
                    }
                }
            }
            $notificationSent = false;
            if ($trip->representative) {
                $deviceTokens = $trip->representative->deviceToken()->pluck('device_token')->toArray();
                foreach ($deviceTokens as $deviceToken) {
                    $notificationRequest = new Request([
                        'device_token' => $deviceToken,
                        'title' => 'تمت إضافتك إلى رحلة',
                        'body' => "تمت إضافتك إلى رحلة جديدة بكود {$trip->code}.",
                        'icon' => '',
                    ]);

                    $response = $this->sendFirebaseNotification($notificationRequest);
                    if ($response->getStatusCode() === 200) {
                        $notificationSent = true;
                    } else {
                        \Log::error('Failed to send notification to representative', [
                            'trip_id' => $trip->id,
                            'representative_id' => $validated['representative_id'],
                            'device_token' => $deviceToken,
                            'error' => $response->getData()->error ?? 'Unknown error',
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.trips.index')->with('success', 'تم إنشاء رحلة المندوب بنجاح' . ($notificationSent ? ' وتم إرسال الإشعار' : '') . ' مع رمز QR');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }


    public function storeRepresentativeTransit(Request $request)
    {
        try {
            $validated = $request->validate([
                'representative_id' => 'required|exists:representatives,id',
                'contents' => 'required|array|min:1',
                'contents.*' => 'exists:trip_shipment_content,id',
                'branch_from' => 'required|exists:branchss,id',
                'city_id' => 'required|exists:regions,id',
                'notes' => 'nullable|string|max:255',
            ], [
                'representative_id.required' => 'يجب اختيار المندوب',
                'contents.required' => 'يجب اختيار محتوى واحد على الأقل',
                'branch_from.required' => 'يجب اختيار الفرع',
                'city_id.required' => 'يجب اختيار المدينة المتوجه إليها',
            ]);

            DB::beginTransaction();

            // Generate trip code
            $branch_from = Branchs::findOrFail($validated['branch_from']);
            $city = Region::findOrFail($validated['city_id']);
            $prefix = strtoupper(substr($branch_from->key, 0, 2));
            $prefix .= strtoupper(substr($city->key, 0, 2));
            $tripCount = Trip::count() + 1;
            $code = $prefix . $tripCount;

            // Get representative for currency
            $representative = Representative::findOrFail($validated['representative_id']);

            // Create trip
            $trip = Trip::create([
                'name' => $validated['notes'] ?? 'رحلة توصيل',
                'status' => 1,
                'representative_id' => $validated['representative_id'],
                'type_coin' => $representative->region->country->currency_id ?? 1,
                'code' => $code,
                'branches_from' => $validated['branch_from'],
                'city_id' => $validated['city_id'],
                'type' => 1, // Transit trip
                'transit' => 1,
                'type_driver' => 1, // Representative
                'admin_id' => auth('admin')->user()->id,
            ]);

            // Generate QR code
            $qrCodePath = 'qr_codes/trip_' . $trip->id . '.png';
            $trip->update(['qr_code' => $qrCodePath]);

            $shipmentIds = [];

            // Process each content
            foreach ($validated['contents'] as $tripContentId) {
                // Get the trip shipment content
                $tripContent = TripShipmentContent::with('shipmentContent.shipment')
                    ->findOrFail($tripContentId);

                if (!$tripContent->shipmentContent) {
                    throw new \Exception("محتوى الشحنة غير موجود");
                }

                $content = $tripContent->shipmentContent;
                $shipment = $content->shipment;

                if (!$shipment) {
                    throw new \Exception("الشحنة المرتبطة غير موجودة");
                }

                // Update trip_shipment_content status and link to trip
                $tripContent->update([
                    'trip_id' => $trip->id,
                    'status' => 2, // With representative
                ]);

                // Update shipment status
                $shipment->update(['status_id' => 2]); // With representative

                // Track shipment IDs
                if (!in_array($shipment->id, $shipmentIds)) {
                    $shipmentIds[] = $shipment->id;
                }

                // Update warehouse
                $warContent = Warehouse::where('trip_id', 0)
                    ->where('shipment_content_id', $content->id)
                    ->first();

                if ($warContent) {
                    $quantity = $tripContent->quantity ?? 1;

                    // Update existing warehouse quantity
                    $newQuantity = max(0, $warContent->quantity - $quantity);
                    $warContent->update([
                        'quantity' => $newQuantity,
                        'finnished' => $newQuantity == 0 ? 1 : 0
                    ]);

                    // Create new warehouse entry for trip
                    Warehouse::create([
                        'branches_from' => $warContent->branches_from,
                        'trip_id' => $trip->id,
                        'shipment_id' => $shipment->id,
                        'shipment_content_id' => $content->id,
                        'status' => 2, // With representative
                        'trip_content_id' => $tripContent->id,
                        'quantity' => $quantity,
                        'finnished' => 0
                    ]);
                }

                // Send notification to client
                $this->notifyClient($shipment, $trip);
            }

            // Attach shipments to trip
            if (!empty($shipmentIds)) {
                $trip->shipments()->sync($shipmentIds);
            }

            // Assign delivery to shipments
            foreach ($shipmentIds as $shipmentId) {
                $this->assignDeliveryToShipment($trip, $shipmentId);
            }

            // Send notification to representative
            $notificationSent = $this->notifyRepresentative($trip);

            DB::commit();

            return redirect()->route('admin.trips.index')
                ->with('success', 'تم إنشاء رحلة المندوب بنجاح' .
                    ($notificationSent ? ' وتم إرسال الإشعار' : ''));
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error creating representative transit trip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Notify client about shipment assignment
     */
    private function notifyClient($shipment, $trip)
    {
        try {
            $client = $shipment->client;
            if (!$client) {
                return;
            }

            $deviceTokens = DB::table('device_tokens')
                ->where('model_type', get_class($client))
                ->where('model_id', $client->id)
                ->pluck('device_token')
                ->toArray();

            foreach ($deviceTokens as $deviceToken) {
                $notificationRequest = new Request([
                    'device_token' => $deviceToken,
                    'title' => 'تأكيد الشحنة',
                    'body' => "تم إضافة شحنتك {$shipment->code} إلى رحلة {$trip->code} بنجاح",
                    'icon' => '',
                ]);

                $response = $this->sendFirebaseNotification($notificationRequest);

                if ($response->getStatusCode() !== 200) {
                    \Log::error('Failed to send notification for shipment', [
                        'shipment_id' => $shipment->id,
                        'client_id' => $client->id,
                        'device_token' => $deviceToken,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error notifying client', [
                'error' => $e->getMessage(),
                'shipment_id' => $shipment->id ?? null
            ]);
        }
    }

    /**
     * Notify representative about trip assignment
     */
    private function notifyRepresentative($trip)
    {
        try {
            if (!$trip->representative) {
                return false;
            }

            $deviceTokens = $trip->representative->deviceToken()
                ->pluck('device_token')
                ->toArray();

            $notificationSent = false;

            foreach ($deviceTokens as $deviceToken) {
                $notificationRequest = new Request([
                    'device_token' => $deviceToken,
                    'title' => 'تمت إضافتك إلى رحلة',
                    'body' => "تمت إضافتك إلى رحلة جديدة بكود {$trip->code}",
                    'icon' => '',
                ]);

                $response = $this->sendFirebaseNotification($notificationRequest);

                if ($response->getStatusCode() === 200) {
                    $notificationSent = true;
                } else {
                    \Log::error('Failed to send notification to representative', [
                        'trip_id' => $trip->id,
                        'representative_id' => $trip->representative_id,
                        'device_token' => $deviceToken,
                    ]);
                }
            }

            return $notificationSent;
        } catch (\Exception $e) {
            \Log::error('Error notifying representative', [
                'error' => $e->getMessage(),
                'trip_id' => $trip->id ?? null
            ]);
            return false;
        }
    }
    public function changeStatus(Request $request, $id)
    {

        try {
            $trip = Trip::findOrFail($id);
            $newStatus = (int) $request->status;
            // if (!in_array($newStatus, [1, 8])) {
            //     return redirect()->back()->with('error', 'هذه الحالة غير مسموحة.');
            // }

            if ($newStatus <= $trip->status) {
                return redirect()->back()->with('error', 'لا يمكن الرجوع إلى حالة سابقة أو نفس الحالة الحالية.');
            }
            DB::beginTransaction();
            $trip->status = $newStatus;
            $trip->save();
            $representative = Representative::find($trip->representative_id);
            $shipments = $trip->shipments;

            if ($shipments->isNotEmpty()) {
                foreach ($shipments as $shipment) {
                    $this->updateShipmentStatus_v2($shipment, $newStatus);
                    // $shipment->status_id = $newStatus;
                    // $shipment->save();
                    if ($shipment->type == 2) {
                        Settlement::create([
                            'trip_id' => $trip->id,
                            'shipment_id' => $shipment->id,
                            'value_representative' => (($shipment->shipping_cost ?? 0) * ($representative->commission / 100)),
                            'value_company' => ($shipment->price ?? 0) - ($shipment->additional_shipping_cost == 1 ? $shipment->shipping_cost : 0),
                            'shipping_company_price' => $shipment->shipping_cost -  (($shipment->shipping_cost ?? 0) * ($representative->commission / 100))
                        ]);
                    }
                }
                if ($request->status == 3) {
                    foreach ($trip->contentsTripV2 as $contentsTrip) {
                        $contentsTrip->status_id = 7;
                        $contentsTrip->taken =  $contentsTrip->quantity;
                        $contentsTrip->save();
                    }
                }


                $client = $shipment->client;
                $shipment->statusHistory()->create([
                    'status_id' => $trip->status,
                    // 'explain' => $shipment->explain ?? null
                ]);

                if ($client) {
                    $deviceTokens = DB::table('device_tokens')
                        ->where('model_type', get_class($client))
                        ->where('model_id', $client->id)
                        ->pluck('device_token')
                        ->toArray();

                    foreach ($deviceTokens as $deviceToken) {
                        $notificationRequest = new Request([
                            'device_token' => $deviceToken,
                            'title' => 'حالة الشحنة',
                            'body' => "تم تحديث حالة شحنتك رقم {$shipment->code} إلى: " . ($trip->statusLabel ?? '--'),
                            'icon' => '',
                        ]);
                        $response = $this->sendFirebaseNotification($notificationRequest);
                        if ($response->getStatusCode() === 200) {
                            $notificationSent = true;
                        } else {
                            Log::error('Failed to send notification for shipment confirmation', [
                                'shipment_id' => $shipment->id,
                                'client_id' => $client->id,
                                'device_token' => $deviceToken,
                                'error' => $response->getData()->error ?? 'Unknown error',
                            ]);
                        }
                    }
                } else {
                    Log::warning('No client associated with shipment for notification', [
                        'shipment_id' => $shipment->id,
                    ]);
                }
            }

            $notificationSent = false;
            if ($trip->representative) {
                $deviceTokens = $trip->representative->deviceToken()->pluck('device_token')->toArray();
                foreach ($deviceTokens as $deviceToken) {
                    $notificationRequest = new Request([
                        'device_token' => $deviceToken,
                        'title' => 'تغيير حالة الرحلة والشحنات',
                        'body' => "تم تغيير حالة الرحلة بكود {$trip->code} إلى " . ($trip->statusLabel ?? 'غير معروف') .
                            " وتم تحديث حالة جميع الشحنات المرتبطة.",
                        'icon' => '',
                    ]);

                    $response = $this->sendFirebaseNotification($notificationRequest);
                    if ($response->getStatusCode() === 200) {
                        $notificationSent = true;
                    } else {
                        \Illuminate\Support\Facades\Log::error('Failed to send status change notification to representative', [
                            'trip_id' => $trip->id,
                            'representative_id' => $trip->representative_id,
                            'device_token' => $deviceToken,
                            'error' => $response->getData()->error ?? 'Unknown error',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('تم تغيير حالة الرحلة والشحنات بنجاح' . ($notificationSent ? ' وتم إرسال الإشعار' : ''));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'الرحلة غير موجودة',
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة. حاول مرة أخرى لاحقًا',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function printPdf($id)
    {
        return $this->generate($id);
    }
    public function qrCode($id)
    {
        return $this->generate_qrcode($id);
    }

    public function show($id)
    {
        $trip = Trip::with([
            'shipments' => function ($query) {
                $query->with('contents');
            },
            'contents' => function ($query) {
                $query->withPivot('quantity');
            },
            'statusHistory',
            'representative',
            'branchFrom',
            'branchTo',
            'region.country'
        ])->findOrFail($id);
        $trip->updateTripStatus($trip);

        return view('Admin.trips.show', compact('trip'));
    }
    public function edit($id)
    {
        $trip = Trip::find($id);
        $representatives = Representative::where('status', true)->get();
        $shipments = Shipment::whereNotIn('status_id', [5, 8])->whereDoesntHave('trips')->get();
        $branches = Branchs::get();
        $regions = Region::get();
        $trip->updateTripStatus($trip);

        return view('Admin.trips.edit', compact('trip', 'representatives', 'shipments', 'branches', 'regions'));
    }

    public function editDriver($id)
    {
        $trip = Trip::find($id);
        $representatives = Representative::where('status', true)->get();
        $shipments = Shipment::whereNotIn('status_id', [5, 8])->whereDoesntHave('trips')->get();
        $branches = Branchs::get();
        $regions = Region::get();
        $trip->updateTripStatus($trip);

        return view('Admin.trips.edit-driver', compact('trip', 'representatives', 'shipments', 'branches', 'regions'));
    }

    public function getTripDetails($id)
    {
        $trip = Trip::with(['representative', 'branchFrom', 'branchTo', 'region', 'shipments'])->findOrFail($id);

        $tripData = [
            'code' => $trip->code,
            'status_label' => $trip->status_label,
            'representative_name' => $trip->representative ? $trip->representative->name : null,
            'branch_from_name' => $trip->branchFrom->name ?? null,
            'branch_to_name' => $trip->branchTo->name ?? null,
            'region_name' => $trip->region->region_ar ?? null,
            'shipments_count' => $trip->shipments_count,
            'created_at' => $trip->created_at->toDateTimeString(),
            'updated_at' => $trip->updated_at->toDateTimeString(),
        ];

        $shipments = $trip->shipments->map(function ($shipment) {
            return [
                'code' => $shipment->code,
                'client_name' => $shipment->name_received,
                'status_id' => $shipment->status_id,
                'weight' => $shipment->weight,
                'calculateTotalCost' => $shipment->calculateTotalCost(),
            ];
        })->all();

        return response()->json(['trip' => $tripData, 'shipments' => $shipments]);
    }

    public function update(Request $request, $id)
    {
        try {
            $trip = Trip::findOrFail($id);

            $validated = $request->validate([
                'branch_from' => 'required|exists:branchss,id',
                'representative_id' => 'nullable|exists:representatives,id',
                //   'type_coin' => 'nullable|string|max:50',
                'shipments' => 'nullable|array',
                'shipments.*' => 'exists:shipments,id',
                'branch_to' => 'nullable|exists:branchss,id',
                'city_id' => 'nullable|exists:regions,id',
                'name' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();
            $representative = null;
            if ($validated['representative_id']) {
                $representative = Representative::find($validated['representative_id']);
            }

            $trip->update([
                'name' => $validated['name'],
                'representative_id' => $validated['representative_id'],
                'type_coin' => $representative ? $representative->region->country->currency_id : $trip->type_coin,
                'branches_from' => $validated['branch_from'],
                'branches_to' => $validated['branch_to'],
                'city_id' => $validated['city_id'],
            ]);

            if (!empty($validated['shipments'])) {
                $trip->shipments()->sync($validated['shipments']);
            } else {
                $trip->shipments()->detach();
            }
            $notificationSent = false;
            if ($validated['representative_id'] && $trip->representative) {
                $deviceTokens = $trip->representative->deviceToken()->pluck('device_token')->toArray();
                foreach ($deviceTokens as $deviceToken) {
                    $notificationRequest = new Request([
                        'device_token' => $deviceToken,
                        'title' => 'تم تعديل الرحلة',
                        'body' => "تم تعديل رحلتك بكود {$trip->code}.",
                        'icon' => '',
                    ]);

                    $response = $this->sendFirebaseNotification($notificationRequest);
                    if ($response->getStatusCode() === 200) {
                        $notificationSent = true;
                    } else {
                        \Illuminate\Support\Facades\Log::error('Failed to send notification to representative', [
                            'trip_id' => $trip->id,
                            'representative_id' => $validated['representative_id'],
                            'device_token' => $deviceToken,
                            'error' => $response->getData()->error ?? 'Unknown error',
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.trips.index')->with('success', 'تم تحديث الرحلة بنجاح' . ($notificationSent ? ' وتم إرسال الإشعار' : ''));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'الرحلة أو الفرع المحدد غير موجود')->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الرحلة. حاول مرة أخرى لاحقًا')->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Summary of destroy
     * @param mixed $id
     */
    public function destroy($id)
    {
        try {
            $trip = Trip::findOrFail($id);

            // الحالات الممنوعة
            $notAllowedStatuses = [3, 5, 7, 8];

            if (in_array($trip->status, $notAllowedStatuses)) {
                return redirect()->back()->with('error', 'لا يمكن حذف الرحلة لأنها في حالة: ' . $trip->status_label);
            }

            DB::beginTransaction();

            // Retrieve all TripShipmentContent records for the trip
            $tripContents = TripShipmentContent::where('trip_id', $trip->id)->get();

            // Restore quantities in ShipmentContent
            foreach ($tripContents as $tripContent) {
                $content = ShipmentContent::findOrFail($tripContent->shipment_content_id);
                // Subtract the quantity assigned to this trip from taken
                $content->taken = max(0, $content->taken - $tripContent->quantity);
                // Recalculate remaining
                $content->remaining = $content->quantity - $content->taken;
                $content->status_id = 0; // Reset status to default
                $content->save();
            }

            // Detach contents from the trip
            $trip->contents()->detach();

            // Handle shipments
            $shipments = $trip->shipments;
            if ($shipments->isNotEmpty()) {
                foreach ($shipments as $shipment) {
                    $shipment->status_id = 0; // Reset to a default or unassigned status
                    $shipment->representative_id = null; // Remove representative assignment
                    $shipment->save();

                    // Notify client
                    $client = $shipment->client;
                    if ($client) {
                        $deviceTokens = DB::table('device_tokens')
                            ->where('model_type', get_class($client))
                            ->where('model_id', $client->id)
                            ->pluck('device_token')
                            ->toArray();

                        foreach ($deviceTokens as $deviceToken) {
                            $notificationRequest = new Request([
                                'device_token' => $deviceToken,
                                'title' => 'إلغاء الرحلة',
                                'body' => "تم إلغاء الرحلة بكود {$trip->code}. شحنتك {$shipment->code} لم تعد مرتبطة بالرحلة.",
                                'icon' => '',
                            ]);
                            try {
                                $response = $this->sendFirebaseNotification($notificationRequest);
                                if ($response->getStatusCode() !== 200) {
                                    Log::error('Failed to send notification for trip deletion', [
                                        'shipment_id' => $shipment->id,
                                        'client_id' => $client->id,
                                        'device_token' => $deviceToken,
                                        'error' => $response->getData()->error ?? 'Unknown error',
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Notification sending failed: ' . $e->getMessage(), [
                                    'shipment_id' => $shipment->id,
                                    'client_id' => $client->id,
                                    'device_token' => $deviceToken,
                                ]);
                            }
                        }
                    } else {
                        Log::warning('No client associated with shipment for notification', [
                            'shipment_id' => $shipment->id,
                        ]);
                    }
                }
                $trip->shipments()->detach();
            }

            // Notify representative
            $notificationSent = false;
            if ($trip->representative) {
                $deviceTokens = $trip->representative->deviceToken()->pluck('device_token')->toArray();
                foreach ($deviceTokens as $deviceToken) {
                    $notificationRequest = new Request([
                        'device_token' => $deviceToken,
                        'title' => 'تم حذف الرحلة',
                        'body' => "تم حذف الرحلة بكود {$trip->code}.",
                        'icon' => '',
                    ]);

                    $response = $this->sendFirebaseNotification($notificationRequest);
                    if ($response->getStatusCode() === 200) {
                        $notificationSent = true;
                    } else {
                        Log::error('Failed to send delete notification to representative', [
                            'trip_id' => $trip->id,
                            'representative_id' => $trip->representative_id,
                            'device_token' => $deviceToken,
                            'error' => $response->getData()->error ?? 'Unknown error',
                        ]);
                    }
                }
            }

            // Delete the trip
            $trip->delete();

            DB::commit();

            return redirect()->back()->with('success', 'تم حذف الرحلة بنجاح' . ($notificationSent ? ' وتم إرسال الإشعار' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting trip: ' . $e->getMessage(), [
                'trip_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الرحلة: ' . $e->getMessage());
        }
    }

    public function getTripByCode(Request $request)
    {
        $code = $request->input('trip_search');

        $trip = Trip::where('code', $code)->first();

        if ($trip) {
            return view('Admin.trips.show', compact('trip'));
        }

        return redirect()->route('admin.home')->with('error', 'لا توجد رحلة بهذا الكود');
    }
    public function changeContentStatus(Request $request, $contentId)
    {
        try {
            $validated = $request->validate([
                'trip_id' => 'required|exists:trips,id',
                'contents' => 'required|array',
                'contents.*.status_id' => 'required|integer|in:0,1,2,3,4,5,6,7,8,9,10,11,12,13,14',
                'contents.*.taken' => 'nullable|integer|min:0',
            ]);

            $lockedStatuses = [7, 11, 12, 13];
            $contents = $validated['contents'];
            $tripId = $validated['trip_id'];

            // Start a transaction
            DB::beginTransaction();

            foreach ($contents as $contentId => $data) {
                // Find the TripShipmentContent record
                $tripContent = TripShipmentContent::where('shipment_content_id', $contentId)
                    ->where('trip_id', $tripId)
                    ->firstOrFail();

                // Prevent changes if the current status is locked
                if (in_array($tripContent->status_id, $lockedStatuses)) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'لا يمكن تغيير حالة المحتوى  لأنها في حالة: ' . $this->getStatusLabel($tripContent->status_id));
                }


                // Validate taken quantity if status_id = 3
                if ($data['status_id'] == 3) {
                    $taken = $data['taken'] ?? 0;
                    if ($taken < 0 || $taken > $tripContent->quantity) {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'الكمية المأخوذة للمحتوى ' . $tripContent->shipment_content->name . ' يجب أن تكون بين 0 و ' . $tripContent->quantity);
                    }
                }
                //  else {
                //     $data['taken'] = 0;
                // }

                $tripContent->status_id = $data['status_id'];
                $tripContent->taken = $data['taken'];
                $tripContent->save();

                // Update related ShipmentContent
                $content = ShipmentContent::findOrFail($tripContent->shipment_content_id);
                //  $content->status_id = $data['status_id'];
                $content->taken = $content->taken - $tripContent->taken + $data['taken'];
                $content->remaining = $content->quantity - $content->taken;
                $content->save();
                $shipment = $content->shipment;
                if ($shipment) {
                    $allContents = ShipmentContent::where('shipment_id', $shipment->id)->get();
                    $allInLockedStatuses = $allContents->every(function ($content) use ($lockedStatuses) {
                        return in_array($content->status_id, $lockedStatuses);
                    });
                    // If all contents are in locked statuses, update shipment status to 8
                    if ($allInLockedStatuses) {
                        $shipment->status_id = 8; // تم الاكتمال
                        $shipment->save();
                    }

                    // Notify client
                    if ($shipment->client) {
                        $deviceTokens = DB::table('device_tokens')
                            ->where('model_type', get_class($shipment->client))
                            ->where('model_id', $shipment->client->id)
                            ->pluck('device_token')
                            ->toArray();

                        $statusMap = [
                            0 => 'غير محدد',
                            1 => 'تحت الإجراء',
                            2 => 'مع المندوب',
                            3 => 'تم التسليم',
                            4 => 'قيد الانتظار',
                            5 => 'رفض الاستلام',
                            6 => 'راجع مع المندوب',
                            7 => 'داخل الفرع',
                            8 => 'تم الاكتمال',
                            9 => 'راجع الفرع',
                            10 => 'مرحلة إلى فرع آخر',
                            11 => 'مفقودة',
                            12 => 'ناقص',
                            13 => 'تلف',
                            14 => 'داخل الجمرك',
                        ];

                        foreach ($deviceTokens as $deviceToken) {
                            $notificationRequest = new Request([
                                'device_token' => $deviceToken,
                                'title' => 'تحديث حالة محتوى الشحنة',
                                'body' => "تم تحديث حالة محتوى الشحنة {$content->name} إلى {$statusMap[$data['status_id']]}" .
                                    ($data['status_id'] == 3 ? " مع كمية مأخوذة {$data['taken']}" : "") .
                                    ($allInLockedStatuses ? " وتم إكمال الشحنة {$shipment->code}." : " في شحنتك {$shipment->code}."),
                                'icon' => '',
                            ]);
                            try {
                                $response = $this->sendFirebaseNotification($notificationRequest);
                                if ($response->getStatusCode() !== 200) {
                                    Log::error('Failed to send notification for content status update', [
                                        'content_id' => $content->id,
                                        'trip_content_id' => $tripContent->id,
                                        'shipment_id' => $shipment->id,
                                        'client_id' => $shipment->client->id,
                                        'device_token' => $deviceToken,
                                        'error' => $response->getData()->error ?? 'Unknown error',
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Notification sending failed: ' . $e->getMessage(), [
                                    'content_id' => $content->id,
                                    'trip_content_id' => $tripContent->id,
                                    'shipment_id' => $shipment->id,
                                    'client_id' => $shipment->client->id,
                                    'device_token' => $deviceToken,
                                ]);
                            }
                        }
                    }

                    $tripWar = Trip::find($tripId);

                    $warehouse = Warehouse::where('trip_id', $tripId)->where('shipment_content_id', $contentId)->first();
                    if (in_array($tripContent->status_id, [10, 7])) {
                        $warehouse?->update(['status' => 2, 'branches_from' => $tripWar->branches_to, 'quantity' => $warehouse->quantity - $data['taken']]);
                        Warehouse::create(['trip_id' => $tripId, 'shipment_id' => $content->shipment->id, 'shipment_content_id' => $content->id, 'trip_content_id' => $tripContent->id, 'branches_from' => $tripWar->branches_to, 'quantity' => $data['taken'], 'status' => 2, 'finnished' => 1]);
                    } elseif (in_array($tripContent->status_id, [11, 12])) {
                        $warehouse?->update(['status' => 5, 'branches_from' => $tripWar->branches_to, 'quantity' => $warehouse->quantity - $data['taken']]);
                        Warehouse::create(['trip_id' => $tripId, 'shipment_id' => $content->shipment->id, 'shipment_content_id' => $content->id, 'trip_content_id' => $tripContent->id, 'branches_from' => $tripWar->branches_to, 'quantity' => $data['taken'], 'status' => 5, 'finnished' => 1]);
                    } elseif ($tripContent->status_id == 13) {
                        $warehouse?->update(['status' => 4, 'branches_from' => $tripWar->branches_to, 'quantity' => $warehouse->quantity - $data['taken']]);
                        Warehouse::create(['trip_id' => $tripId, 'shipment_id' => $content->shipment->id, 'shipment_content_id' => $content->id, 'trip_content_id' => $tripContent->id, 'branches_from' => $tripWar->branches_to, 'quantity' => $data['taken'], 'status' => 4, 'finnished' => 1]);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'تم تحديث حالات المحتويات بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating content statuses: ' . $e->getMessage(), [
                'trip_id' => $request->input('trip_id'),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث حالات المحتويات: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to get status label
     */
    private function getStatusLabel($statusId)
    {
        $statusMap = [
            0 => 'غير محدد',
            1 => 'تحت الإجراء',
            2 => 'مع المندوب',
            3 => 'تم التسليم',
            4 => 'قيد الانتظار',
            5 => 'رفض الاستلام',
            6 => 'راجع مع المندوب',
            7 => 'داخل الفرع',
            8 => 'تم الاكتمال',
            9 => 'راجع الفرع',
            10 => 'مرحلة إلى فرع آخر',
            11 => 'مفقودة',
            12 => 'ناقص',
            13 => 'تلف',
            14 => 'داخل الجمرك',
        ];

        return $statusMap[$statusId] ?? 'غير معروف';
    }
}
