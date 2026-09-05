<?php

namespace App\Http\Controllers\Admin;

use App\Models\Store;
use App\Models\Branchs;
use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Models\TripShipmentContent;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    // public function index()
    // {
    //     $stores = Store::with('shipment')->get();
    //     $branchs = Branchs::get();

    //     $shipments = null;
    //     if (in_array(auth()->user()->role, [0])) {
    //         $path = 'Admin.branchs.index';
    //     } else {
    //         $shipments = Shipment::where('branches_from', auth()->user()->branch_id)->orderBy('created_at', 'desc')->get();
    //         $path = 'Admin.store.index';
    //     }

    //     return view($path, compact('stores', 'shipments', 'branchs'));
    // }
    public function index()
    {
        $stores = Store::with('shipment')->get();
        $branchs = Branchs::get();
        $userBranchId = auth()->user()->branch_id;

        if (in_array(auth()->user()->role, [0]) || is_null($userBranchId)) {
            $shipments = Shipment::with(['person'])
                ->whereHas('warehouse')
                ->orderBy('created_at', 'desc')
                ->get();

            $warehouseData = Warehouse::with(['shContent', 'trContent'])
                ->whereIn('shipment_id', $shipments->pluck('id'))
                ->get()
                ->groupBy('shipment_id');

            $path = 'Admin.branchs.index';
        } else {
            if (!$userBranchId) {
                return redirect()->back()->with('error', 'لم يتم تعيين فرع للمستخدم');
            }

            $shipments = Shipment::with(['person'])
                ->whereHas('warehouse', function ($q) use ($userBranchId) {
                    $q->where('branches_from', $userBranchId);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $warehouseData = Warehouse::with(['shContent', 'trContent'])
                ->where('branches_from', $userBranchId)
                //  ->where('finnished', 1)
                // ->whereNotNull('trip_id')
                ->whereIn('shipment_id', $shipments->pluck('id'))
                ->get()
                ->groupBy('shipment_id');
            $path = 'Admin.store.index';
        }

        return view($path, compact('stores', 'branchs', 'shipments', 'warehouseData'));
    }
    public function stageBranch()
    {
        $branchId = Auth::user()->branch_id;
        $contents = TripShipmentContent::where('status_id', 10)
            ->whereHas('trip', function ($query) use ($branchId) {
                $query->where('branches_to', $branchId);
            })
            ->with([
                'shipmentContent' => function ($query) {
                    $query->select('id', 'shipment_id', 'code', 'name', 'barcode');
                },
                'shipmentContent.shipment' => function ($query) {
                    $query->select('id', 'code', 'branches_from', 'branches_to');
                },
                'trip' => function ($query) {
                    $query->select('id', 'code', 'branches_from', 'branches_to');
                },
                'trip.branchFrom' => function ($query) {
                    $query->select('id', 'name');
                },
                'trip.branchTo' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        $branches = Branchs::select('id', 'name')->get();
        return view('Admin.store.stageBranch', compact('contents', 'branches'));
    }
    public function create()
    {
        $shipments = Shipment::all();
        if (auth()->user()->branch_id) {
            $branches = Branchs::find(auth()->user()->branch_id);
        } else {
            $branches = Branchs::all();
        }
        return view('Admin.stores.create', compact('shipments', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'shipment_id' => 'nullable|exists:shipments,id',
            'branch_id' => 'nullable',

        ]);

        try {
            Store::create($validated);
            return redirect()->route('admin.stores.index')->with('success', 'تم إضافة المخزن بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة المخزن')->withInput();
        }
    }

    public function edit(Store $store)
    {
        $shipments = Shipment::all();
        if (auth()->user()->branch_id) {
            $branches = Branchs::find(auth()->user()->branch_id);
        } else {
            $branches = Branchs::all();
        }
        return view('Admin.stores.edit', compact('store', 'shipments', 'branches'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'branch_id' => 'nullable',
            'quantity' => 'required|integer|min:0',
            'shipment_id' => 'nullable|exists:shipments,id',
        ]);

        try {
            $store->update($validated);
            return redirect()->route('admin.stores.index')->with('success', 'تم تحديث المخزن بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث المخزن')->withInput();
        }
    }

    public function destroy(Store $store)
    {
        try {
            $store->delete();
            return redirect()->route('admin.stores.index')->with('success', 'تم حذف المخزن بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف المخزن');
        }
    }

    public function outgoing()
    {
        $shipments = Shipment::where('branches_from', auth()->user()->branch_id)->whereHas('contents')->orderByDesc('created_at')->get();

        return view('Admin.stores.outgoing', compact('shipments'));
    }
    // public function incoming()
    // {
    //     try {
    //         $userBranchId = auth()->user()->branch_id;

    //         $shipments = Shipment::whereHas('contents.tripShipmentContents.trip', function ($query) use ($userBranchId) {
    //             $query->where('branches_to', $userBranchId);
    //         })
    //             ->with([
    //                 'contents' => function ($query) {
    //                     $query->select('id', 'shipment_id', 'code', 'name', 'price', 'quantity', 'taken', 'remaining', 'status_id');
    //                 },
    //                 'person'
    //             ])
    //             ->get();

    //         return view('Admin.stores.incoming', compact('shipments'));
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'حدث خطأ أثناء جلب الشحنات: ' . $e->getMessage());
    //     }
    // }

    public function incoming()
    {
        try {
            $userBranchId = auth()->user()->branch_id;

            $shipments = Shipment::whereHas('warehouse.trip', function ($query) use ($userBranchId) {
                $query->where('branches_to', $userBranchId);
            })
                ->with([
                    'warehouse' => function ($query) {
                        $query->whereNotNull('trip_id')->where('finnished', 1)
                            ->with([
                                'shContent' => function ($query) {
                                    $query->select('id', 'shipment_id', 'code', 'name', 'price');
                                },
                                'trContent' => function ($query) {
                                    $query->select('id', 'trip_id', 'shipment_content_id', 'quantity', 'taken', 'status_id')
                                        ->with(['shipmentContent' => function ($query) {
                                            $query->select('id', 'shipment_id', 'code', 'name', 'price');
                                        }]);
                                },
                                'trip' => function ($query) {
                                    $query->select('id', 'branches_to');
                                }
                            ]);
                    },
                    'person' => function ($query) {
                        $query->select('id', 'name');
                    }
                ])->orderByDesc('created_at')
                ->get();
            return view('Admin.stores.incoming', compact('shipments'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب الشحنات: ' . $e->getMessage());
        }
    }
}
