<?php

namespace App\Http\Controllers\Admin;

use App\Models\Currency;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackagePriceController extends Controller
{
    use HandlesTranslatedFields;

    public function byPackage(Package $package): View
    {
        $packagePrices = PackagePrice::query()
            ->with('currency')
            ->where('package_id', $package->id)
            ->latest()
            ->paginate($this->perPage(request()))
            ->withQueryString();

        return $this->view('admin.package-prices.by-package', compact('package', 'packagePrices'));
    }

    public function index(Request $request): View
    {
        $packagePrices = PackagePrice::query()
            ->with(['package', 'currency'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch($query, ['label', 'season_name', 'notes'], $request->string('q'));
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.package-prices.index', compact('packagePrices'));
    }

    public function create(): View
    {
        $packages = Package::all();
        $currencies = Currency::all();
        return $this->view('admin.package-prices.create', compact('packages', 'currencies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['nullable', 'integer'],
            'label' => ['nullable', 'string'],
            'season_name' => ['nullable', 'string'],
            'price_type' => ['nullable', 'string'],
            'room_type' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'currency_id' => ['nullable', 'integer'],
            'pax_min' => ['nullable', 'integer'],
            'pax_max' => ['nullable', 'integer'],
            'group_size_min' => ['nullable', 'integer'],
            'group_size_max' => ['nullable', 'integer'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, ['label', 'season_name', 'notes']);

        PackagePrice::create($data);

        return $this->success('admin.package-prices.index', 'PackagePrice created.');
    }

    public function show(PackagePrice $packagePrice): View
    {
        return $this->view('admin.package-prices.show', compact('packagePrice'));
    }

    public function edit(PackagePrice $packagePrice): View
    {
        $packages = Package::all();
        $currencies = Currency::all();
        return $this->view('admin.package-prices.edit', compact('packagePrice', 'packages', 'currencies'));
    }

    public function update(Request $request, PackagePrice $packagePrice): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['nullable', 'integer'],
            'label' => ['nullable', 'string'],
            'season_name' => ['nullable', 'string'],
            'price_type' => ['nullable', 'string'],
            'room_type' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'currency_id' => ['nullable', 'integer'],
            'pax_min' => ['nullable', 'integer'],
            'pax_max' => ['nullable', 'integer'],
            'group_size_min' => ['nullable', 'integer'],
            'group_size_max' => ['nullable', 'integer'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, ['label', 'season_name', 'notes']);

        $packagePrice->update($data);

        return $this->success('admin.package-prices.index', 'PackagePrice updated.');
    }

    public function destroy(PackagePrice $packagePrice): RedirectResponse
    {
        $packagePrice->delete();

        return $this->success('admin.package-prices.index', 'PackagePrice deleted.');
    }

    protected function perPage(Request $request, int $default = 15): int
    {
        return max(5, min((int) $request->input('per_page', $default), 100));
    }
}
