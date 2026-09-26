<div class="pricing-type-block" id="travelPackagePricingBlock" data-pricing-type="travel_package">
    <div class="pricing-card-wrapper mb-4 p-3 w-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold mb-1 text-primary"><i class="la la-hotel me-1"></i>
                    {{ __('Accommodation & Season Pricing') }}</h6>
                <small
                    class="text-light opacity-75">{{ __('Add accommodation categories (Standard, Deluxe, Luxury), season prices, and hotel assignments by city.') }}</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddAccommodation">
                <i class="ti ti-plus"></i> {{ __('Add New Accommodation Tier') }}
            </button>
        </div>

        <div id="accommodationsWrapper" class="stack-list">
            @php
                $accommodations = old(
                    'tour_package_accommodations',
                    isset($package) ? $package->tourPackageAccommodations ?? [] : [],
                );
                if (collect($accommodations)->isEmpty()) {
                    $accommodations = collect([
                        ['name' => 'Standard Accommodations', 'seasons' => [], 'hotels' => []],
                        ['name' => 'Deluxe Accommodations', 'seasons' => [], 'hotels' => []],
                        ['name' => 'Ultra Deluxe Accommodations', 'seasons' => [], 'hotels' => []],
                        ['name' => 'Luxury Accommodations', 'seasons' => [], 'hotels' => []],
                    ]);
                }
            @endphp
            @forelse ($accommodations as $accIndex => $acc)
                @php
                    $accId = is_object($acc) ? $acc->id : $acc['id'] ?? null;
                    $accName = is_object($acc) ? $acc->name : $acc['name'] ?? '';
                    $accDesc = is_object($acc) ? $acc->description : $acc['description'] ?? '';
                    $seasons = is_object($acc) ? $acc->seasons : $acc['seasons'] ?? [];
                    $hotels = is_object($acc) ? $acc->hotels : $acc['hotels'] ?? [];
                @endphp
                <div class="repeat-box accommodation-row mb-4 p-3" data-acc-index="{{ $accIndex }}">
                    @if ($accId)
                        <input type="hidden" name="tour_package_accommodations[{{ $accIndex }}][id]"
                            value="{{ $accId }}">
                    @endif
                    <div
                        class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom border-secondary">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary"><i
                                    class="ti ti-building me-1"></i>{{ __('Accommodation Tier') }} #<span
                                    class="acc-number">{{ $accIndex + 1 }}</span></span>
                            <input type="text" name="tour_package_accommodations[{{ $accIndex }}][name]"
                                class="form-control form-control-sm fw-bold" value="{{ $accName }}"
                                placeholder="e.g. 5-Star Standard Accommodations" style="min-width: 280px;">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-accommodation"><i
                                class="ti ti-trash"></i> {{ __('Delete Tier') }}</button>
                    </div>

                    <div class="mb-3">
                        <label
                            class="form-label small fw-bold text-light">{{ __('Tier Description (Optional)') }}</label>
                        <input type="text" name="tour_package_accommodations[{{ $accIndex }}][description]"
                            class="form-control form-control-sm" value="{{ $accDesc }}"
                            placeholder="e.g. Includes breakfast and transfers">
                    </div>

                    <!-- Seasons & Occupancy Pricing -->
                    <div class="repeat-box p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-white mb-0"><i class="ti ti-calendar me-1"></i>
                                {{ __('Seasons & Occupancy Prices') }}</h6>
                            <button type="button" class="btn btn-xs btn-outline-primary js-add-acc-season"
                                data-acc-index="{{ $accIndex }}">+ {{ __('Add New Season') }}</button>
                        </div>

                        <div class="acc-seasons-wrapper stack-list">
                            @foreach ($seasons as $sIndex => $season)
                                @php
                                    $sId = is_object($season) ? $season->id : $season['id'] ?? null;
                                    $sName = is_object($season)
                                        ? $season->display_season_name
                                        : (is_array($season['name'] ?? null)
                                            ? $season['name']['en'] ?? ''
                                            : $season['name'] ?? '');
                                    $dateFrom = is_object($season)
                                        ? $season->date_from?->format('Y-m-d') ?? ''
                                        : $season['date_from'] ?? '';
                                    $dateTo = is_object($season)
                                        ? $season->date_to?->format('Y-m-d') ?? ''
                                        : $season['date_to'] ?? '';
                                    $period = is_object($season) ? $season->period : ($season['period'] ?? '');
                                    $currencyId = is_object($season) ? $season->currency_id : ($season['currency_id'] ?? null);
                                    $items = is_object($season) ? $season->items : $season['items'] ?? [];
                                @endphp
                                <div class="repeat-box season-row mb-3 p-3">
                                    @if ($sId)
                                        <input type="hidden"
                                            name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][id]"
                                            value="{{ $sId }}">
                                    @endif
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="row g-2 align-items-center flex-grow-1 me-2">
                                            <div class="col-md-3">
                                                <input type="text"
                                                    name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][period]"
                                                    class="form-control form-control-sm fw-bold"
                                                    value="{{ $period }}" placeholder="May to August">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text"
                                                    name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][name][en]"
                                                    class="form-control form-control-sm fw-bold"
                                                    value="{{ $sName }}"
                                                    placeholder="e.g. May to August (Low Season)">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="date"
                                                    name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][date_from]"
                                                    class="form-control form-control-sm" value="{{ $dateFrom }}"
                                                    placeholder="From date">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="date"
                                                    name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][date_to]"
                                                    class="form-control form-control-sm" value="{{ $dateTo }}"
                                                    placeholder="To date">
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-select form-select-sm" name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][currency_id]">
                                                    <option value="">{{ __('Package currency') }}</option>
                                                    @foreach ($currencies ?? collect() as $currency)
                                                        <option value="{{ $currency->id }}" {{ (int) $currencyId === (int) $currency->id ? 'selected' : '' }}>{{ $currency->code }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-season"><i
                                                class="ti ti-x"></i></button>
                                    </div>

                                    <!-- Occupancy Items Grid -->
                                    <div class="row g-2 mt-2">
                                        @php
                                            $occupancies = [
                                                'triple' => __('Per Person in Triple Room'),
                                                'double' => __('Per Person in Double Room'),
                                                'single' => __('Single Room Supplement'),
                                            ];
                                            $itemPrices = [];
                                            foreach ((array) $items as $item) {
                                                $occType = is_object($item)
                                                    ? $item->occupancy_type
                                                    : $item['occupancy_type'] ?? '';
                                                $priceVal = is_object($item) ? $item->price : $item['price'] ?? '';
                                                if ($occType) {
                                                    $itemPrices[$occType] = $priceVal;
                                                }
                                            }
                                        @endphp
                                        @foreach ($occupancies as $occKey => $occLabel)
                                            <div class="col-md-4">
                                                <label
                                                    class="form-label small text-light mb-1">{{ $occLabel }}</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">$</span>
                                                    <input type="hidden"
                                                        name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][items][{{ $loop->index }}][occupancy_type]"
                                                        value="{{ $occKey }}">
                                                    <input type="hidden"
                                                        name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][items][{{ $loop->index }}][label][en]"
                                                        value="{{ $occLabel }}">
                                                    <input type="number" step="0.01" min="0"
                                                        name="tour_package_accommodations[{{ $accIndex }}][seasons][{{ $sIndex }}][items][{{ $loop->index }}][price]"
                                                        class="form-control" value="{{ $itemPrices[$occKey] ?? '' }}"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Assigned Hotels per City -->
                    <div class="repeat-box p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-white mb-0"><i class="ti ti-map-pin me-1"></i>
                                {{ __('Assigned Hotels per City') }}</h6>
                            <button type="button" class="btn btn-xs btn-outline-primary js-add-acc-hotel"
                                data-acc-index="{{ $accIndex }}">+ {{ __('Add New Hotel') }}</button>
                        </div>

                        <div class="acc-hotels-wrapper stack-list">
                            @foreach ($hotels as $hIndex => $hotel)
                                @php
                                    $cityName = is_object($hotel)
                                        ? ($hotel->city_name ?:
                                        $hotel->city?->display_name)
                                        : $hotel['city_name'] ?? '';
                                    $hotelName = is_object($hotel) ? $hotel->hotel_name : $hotel['hotel_name'] ?? '';
                                    $starRating = is_object($hotel) ? $hotel->star_rating : $hotel['star_rating'] ?? 5;
                                @endphp
                                <div class="repeat-box hotel-row mb-2 p-2">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-3">
                                            <input type="text"
                                                name="tour_package_accommodations[{{ $accIndex }}][hotels][{{ $hIndex }}][city_name]"
                                                class="form-control form-control-sm" value="{{ $cityName }}"
                                                placeholder="e.g. Cairo / Luxor / Aswan">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text"
                                                name="tour_package_accommodations[{{ $accIndex }}][hotels][{{ $hIndex }}][hotel_name]"
                                                class="form-control form-control-sm" value="{{ $hotelName }}"
                                                placeholder="e.g. Aracan Eatabe / Pyramisa Isis or similar">
                                        </div>
                                        <div class="col-md-3">
                                            <select
                                                name="tour_package_accommodations[{{ $accIndex }}][hotels][{{ $hIndex }}][star_rating]"
                                                class="form-select form-select-sm">
                                                <option value="5"
                                                    {{ (int) $starRating === 5 ? 'selected' : '' }}>
                                                    5 Stars ★★★★★</option>
                                                <option value="4"
                                                    {{ (int) $starRating === 4 ? 'selected' : '' }}>
                                                    4 Stars ★★★★</option>
                                                <option value="3"
                                                    {{ (int) $starRating === 3 ? 'selected' : '' }}>
                                                    3 Stars ★★★</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger js-remove-hotel"><i
                                                    class="ti ti-x"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="repeat-box text-center py-4 text-light opacity-75" id="emptyAccommodationsNotice">
                    <i class="ti ti-building fs-2 d-block mb-1"></i>
                    <p class="mb-0">
                        {{ __('No accommodation tier added yet. Click "Add New Accommodation Tier" to get started.') }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('travelPackagePricingBlock');
    if (!root) return;
    const currencies = @json(collect($currencies ?? [])->map(fn ($c) => ['id' => $c->id, 'code' => $c->code])->values());
    const currencyOptions = () => '<option value="">{{ __('Package currency') }}</option>' + currencies.map(c => `<option value="${c.id}">${c.code}</option>`).join('');
    const priceFields = (a, s) => Object.entries({triple:'{{ __('Per Person in Triple Room') }}',double:'{{ __('Per Person in Double Room') }}',single:'{{ __('Per Person in Single Room') }}'}).map(([key,label], i) => `<div class="col-md-4"><label class="form-label small text-light mb-1">${label}</label><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="hidden" name="tour_package_accommodations[${a}][seasons][${s}][items][${i}][occupancy_type]" value="${key}"><input type="hidden" name="tour_package_accommodations[${a}][seasons][${s}][items][${i}][label][en]" value="${label}"><input type="number" step="0.01" min="0" name="tour_package_accommodations[${a}][seasons][${s}][items][${i}][price]" class="form-control" placeholder="{{ __('Empty = hidden') }}"></div></div>`).join('');
    const season = (a, s) => `<div class="repeat-box season-row mb-3 p-3"><div class="row g-2 align-items-center"><div class="col-md-3"><input name="tour_package_accommodations[${a}][seasons][${s}][period]" class="form-control form-control-sm fw-bold" placeholder="May to August"></div><div class="col-md-3"><input name="tour_package_accommodations[${a}][seasons][${s}][name][en]" class="form-control form-control-sm" placeholder="{{ __('Optional title') }}"></div><div class="col-md-2"><input type="date" name="tour_package_accommodations[${a}][seasons][${s}][date_from]" class="form-control form-control-sm"></div><div class="col-md-2"><input type="date" name="tour_package_accommodations[${a}][seasons][${s}][date_to]" class="form-control form-control-sm"></div><div class="col-md-2"><select name="tour_package_accommodations[${a}][seasons][${s}][currency_id]" class="form-select form-select-sm">${currencyOptions()}</select></div></div><button type="button" class="btn btn-sm btn-outline-danger js-remove-season mt-2">{{ __('Delete Period') }}</button><div class="row g-2 mt-2">${priceFields(a,s)}</div></div>`;
    const hotel = (a, h) => `<div class="repeat-box hotel-row mb-2 p-2"><div class="row g-2 align-items-center"><div class="col-md-3"><input name="tour_package_accommodations[${a}][hotels][${h}][city_name]" class="form-control form-control-sm" placeholder="Cairo / Luxor"></div><div class="col-md-5"><input name="tour_package_accommodations[${a}][hotels][${h}][hotel_name]" class="form-control form-control-sm" placeholder="{{ __('Hotel name') }}"></div><div class="col-md-3"><select name="tour_package_accommodations[${a}][hotels][${h}][star_rating]" class="form-select form-select-sm"><option value="5">5 Stars ★★★★★</option><option value="4">4 Stars ★★★★</option><option value="3">3 Stars ★★★</option></select></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger js-remove-hotel">&times;</button></div></div></div>`;
    const tier = a => `<div class="repeat-box accommodation-row mb-4 p-3" data-acc-index="${a}"><div class="d-flex gap-2 mb-3"><input name="tour_package_accommodations[${a}][name]" class="form-control fw-bold" placeholder="{{ __('Accommodation level') }}"><button type="button" class="btn btn-outline-danger js-remove-accommodation">{{ __('Delete') }}</button></div><div class="repeat-box p-3 mb-3"><div class="d-flex justify-content-between mb-2"><strong>{{ __('Seasons & Occupancy Prices') }}</strong><button type="button" class="btn btn-xs btn-outline-primary js-add-acc-season">+ {{ __('Month Period') }}</button></div><div class="acc-seasons-wrapper"></div></div><div class="repeat-box p-3"><div class="d-flex justify-content-between mb-2"><strong>{{ __('Assigned Hotels per City') }}</strong><button type="button" class="btn btn-xs btn-outline-primary js-add-acc-hotel">+ {{ __('Hotel') }}</button></div><div class="acc-hotels-wrapper"></div></div></div>`;

    root.addEventListener('click', event => {
        let button = event.target.closest('#btnAddAccommodation');
        if (button) {
            const list = root.querySelector('#accommodationsWrapper');
            list.querySelector('#emptyAccommodationsNotice')?.remove();
            const index = Math.max(-1, ...[...list.querySelectorAll(':scope > .accommodation-row')].map(row => Number(row.dataset.accIndex))) + 1;
            list.insertAdjacentHTML('beforeend', tier(index)); return;
        }
        button = event.target.closest('.js-add-acc-season');
        if (button) { const row=button.closest('.accommodation-row'), list=row.querySelector('.acc-seasons-wrapper'), index=list.children.length; list.insertAdjacentHTML('beforeend',season(Number(row.dataset.accIndex),index)); return; }
        button = event.target.closest('.js-add-acc-hotel');
        if (button) { const row=button.closest('.accommodation-row'), list=row.querySelector('.acc-hotels-wrapper'), index=list.children.length; list.insertAdjacentHTML('beforeend',hotel(Number(row.dataset.accIndex),index)); return; }
        button = event.target.closest('.js-remove-season,.js-remove-hotel,.js-remove-accommodation');
        if (button) button.closest('.season-row,.hotel-row,.accommodation-row')?.remove();
    });
});
</script>
@endonce
