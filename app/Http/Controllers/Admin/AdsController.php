<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ads;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdsController extends Controller
{
    public function index(Request $request)
    {
        $query = Ads::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $ads = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total' => Ads::count(),
            'types_count' => Ads::distinct('type')->count('type'),
            'with_icons' => Ads::whereNotNull('icon')->count()
        ];

        return view('Admin.ads.index', compact('ads', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'icon' => 'required|string',
            'description' => 'required|string'
        ]);

        Ads::create($request->all());

        return redirect()->route('admin.ads.index')->with('success', 'تم إضافة الإعلان بنجاح');
    }

    public function show($id)
    {
        $ad = Ads::findOrFail($id);
        return response()->json($ad);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string',
            'icon' => 'required|string',
            'description' => 'required|string'
        ]);

        $ad = Ads::findOrFail($id);
        $ad->update($request->all());

        return redirect()->route('admin.ads.index')->with('success', 'تم تحديث الإعلان بنجاح');
    }

    public function destroy($id)
    {
        $ad = Ads::findOrFail($id);
        $ad->delete();

        return response()->json(['success' => 'تم حذف الإعلان بنجاح']);
    }
}
