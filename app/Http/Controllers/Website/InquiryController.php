<?php

namespace App\Http\Controllers\Website;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InquiryController extends BaseWebsiteController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'travel_date' => ['nullable', 'date'],
            'daterange-single' => ['nullable', 'string', 'max:120'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'child' => ['nullable', 'integer', 'min:0'],
            'children' => ['nullable', 'integer', 'min:0'],
            'infants' => ['nullable', 'integer', 'min:0'],
            'comment' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $travelDate = $request->input('travel_date');
        if (!$travelDate && $request->filled('daterange-single')) {
            try {
                $travelDate = date('Y-m-d', strtotime($request->input('daterange-single')));
            } catch (\Throwable $e) {
                $travelDate = null;
            }
        }

        $package = !empty($validated['package_id'])
            ? Package::query()->with('currency')->find($validated['package_id'])
            : null;

        $adults = (int) $request->input('adults', 1);
        $children = (int) $request->input('children', $request->input('child', 0));
        $infants = (int) $request->input('infants', 0);

        $tierKey = match (true) {
            $adults <= 1 => '1_person',
            $adults === 2 => '2_persons',
            $adults === 3 => '3_persons',
            $adults === 4 => '4_persons',
            $adults === 5 => '5_persons',
            default => '6_plus_persons',
        };

        $calculatedTotal = 0;
        $tierSummaryText = null;

        if ($package) {
            $groupTiers = collect($package->group_pricing_tiers)->keyBy('id');
            $tierData = $groupTiers->get($tierKey) ?: $groupTiers->first();

            if ($tierData) {
                $pricePerPerson = (float) $tierData['price_per_person'];
                $calculatedTotal = $adults * $pricePerPerson;
                
                $tierSummaryText = sprintf(
                    'Pricing Tier: %s (%d %s) @ %s%s/person = %s%s total',
                    $tierData['title'],
                    $adults,
                    $adults === 1 ? 'Person' : 'Persons',
                    $package->currency?->symbol ?? '$',
                    number_format($pricePerPerson, 2),
                    $package->currency?->symbol ?? '$',
                    number_format($calculatedTotal, 2)
                );
            }
        }

        $data = [
            'package_id' => $request->input('package_id'),
            'inquiry_type' => 'package',
            'full_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $request->input('phone'),
            'country_name' => $request->input('nationality'),
            'travel_date' => $travelDate,
            'budget' => $calculatedTotal > 0 ? $calculatedTotal : null,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'source' => url()->previous(),
            'message' => trim(collect([
                $request->input('comment') ?: $request->input('message') ?: '',
                $request->input('title') ? 'Tour: ' . $request->input('title') : null,
                $tierSummaryText,
            ])->filter()->implode("\n\n")),
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        /* حماية لو نسخة قاعدة البيانات عندك ناقصة أعمدة */
        $columns = Schema::getColumnListing('inquiries');
        $filteredData = array_filter($data, fn($key) => in_array($key, $columns, true), ARRAY_FILTER_USE_KEY);

        DB::table('inquiries')->insert($filteredData);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => __('Your inquiry has been submitted successfully! We will contact you soon.'),
            ]);
        }

        return redirect()->back()->with('success', __('Your inquiry has been submitted successfully! We will contact you soon.'));
    }
}
