@php
    $cruiseFares = old('tour_package_accommodations', isset($package) ? $package->tourPackageAccommodations : collect());
    if (collect($cruiseFares)->isEmpty()) {
        $cruiseFares = collect(['Standard Accommodations', 'Deluxe Accommodations', 'Ultra Deluxe Accommodations', 'Luxury Accommodations'])
            ->map(fn ($name) => ['name' => $name, 'seasons' => []]);
    }
    $fareOccupancies = ['triple' => __('Triple Cabin'), 'double' => __('Double Cabin'), 'single' => __('Single Cabin')];
@endphp

<div class="pricing-type-block" id="nileCruisePricingBlock" data-pricing-type="nile_cruise">
    <div class="pricing-card-wrapper mb-4 p-3 w-100">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h6 class="fw-bold text-primary mb-1"><i class="la la-ship me-1"></i>{{ __('Nile Cruise Pricing & Packages') }}</h6>
                <small class="text-light opacity-75">{{ __('Empty accommodation levels and empty prices will not appear on the website.') }}</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-fare-add-tier>+ {{ __('Accommodation Level') }}</button>
        </div>

        <div class="stack-list" data-fare-tiers>
            @foreach ($cruiseFares as $a => $acc)
                @php
                    $accId = is_object($acc) ? $acc->id : ($acc['id'] ?? null);
                    $accName = is_object($acc) ? $acc->name : ($acc['name'] ?? '');
                    $seasons = is_object($acc) ? $acc->seasons : ($acc['seasons'] ?? []);
                @endphp
                <div class="repeat-box p-3 mb-4" data-fare-tier data-index="{{ $a }}">
                    @if ($accId)
                        <input type="hidden" name="tour_package_accommodations[{{ $a }}][id]" value="{{ $accId }}">
                    @endif
                    <div class="d-flex gap-2 mb-3">
                        <input class="form-control fw-bold" name="tour_package_accommodations[{{ $a }}][name]" value="{{ $accName }}" placeholder="{{ __('Accommodation level') }}">
                        <button type="button" class="btn btn-outline-danger" data-fare-remove>{{ __('Delete') }}</button>
                    </div>
                    <div data-fare-periods>
                        @foreach ($seasons as $s => $season)
                            @php
                                $seasonId = is_object($season) ? $season->id : ($season['id'] ?? null);
                                $seasonName = is_object($season) ? $season->display_season_name : data_get($season, 'name.en', data_get($season, 'name', ''));
                                $period = is_object($season) ? $season->period : ($season['period'] ?? '');
                                $dateFrom = is_object($season) ? optional($season->date_from)->format('Y-m-d') : ($season['date_from'] ?? '');
                                $dateTo = is_object($season) ? optional($season->date_to)->format('Y-m-d') : ($season['date_to'] ?? '');
                                $currencyId = is_object($season) ? $season->currency_id : ($season['currency_id'] ?? null);
                                $items = is_object($season) ? $season->items : ($season['items'] ?? []);
                                $prices = collect($items)->mapWithKeys(function ($item) {
                                    $key = is_object($item) ? $item->occupancy_type : ($item['occupancy_type'] ?? '');
                                    return [$key => is_object($item) ? $item->price : ($item['price'] ?? '')];
                                });
                            @endphp
                            <div class="repeat-box p-3 mb-3" data-fare-period data-index="{{ $s }}">
                                @if ($seasonId)
                                    <input type="hidden" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][id]" value="{{ $seasonId }}">
                                @endif
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-3"><label class="form-label small">{{ __('Months / period') }}</label><input class="form-control form-control-sm" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][period]" value="{{ $period }}" placeholder="May to August"></div>
                                    <div class="col-lg-2"><label class="form-label small">{{ __('Optional title') }}</label><input class="form-control form-control-sm" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][name][en]" value="{{ $seasonName }}" placeholder="Season"></div>
                                    <div class="col-lg-2"><label class="form-label small">{{ __('From date') }}</label><input type="date" class="form-control form-control-sm" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][date_from]" value="{{ $dateFrom }}"></div>
                                    <div class="col-lg-2"><label class="form-label small">{{ __('To date') }}</label><input type="date" class="form-control form-control-sm" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][date_to]" value="{{ $dateTo }}"></div>
                                    <div class="col-lg-2"><label class="form-label small">{{ __('Currency') }}</label><select class="form-select form-select-sm" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][currency_id]"><option value="">{{ __('Package currency') }}</option>@foreach ($currencies ?? collect() as $currency)<option value="{{ $currency->id }}" {{ (int) $currencyId === (int) $currency->id ? 'selected' : '' }}>{{ $currency->code }}</option>@endforeach</select></div>
                                    <div class="col-lg-1"><button type="button" class="btn btn-sm btn-outline-danger w-100" data-fare-remove>&times;</button></div>
                                </div>
                                <div class="row g-2 mt-2">
                                    @foreach ($fareOccupancies as $key => $label)
                                        <div class="col-md-4"><label class="form-label small">{{ $label }}</label><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="hidden" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][items][{{ $loop->index }}][occupancy_type]" value="{{ $key }}"><input type="hidden" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][items][{{ $loop->index }}][label][en]" value="{{ $label }}"><input type="number" min="0" step="0.01" class="form-control" name="tour_package_accommodations[{{ $a }}][seasons][{{ $s }}][items][{{ $loop->index }}][price]" value="{{ $prices->get($key) }}" placeholder="{{ __('Empty = hidden') }}"></div></div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-fare-add-period>+ {{ __('Month Period') }}</button>
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('nileCruisePricingBlock');
    if (!root) return;
    const currencies = @json(collect($currencies ?? [])->map(fn ($c) => ['id' => $c->id, 'code' => $c->code])->values());
    const occupancyLabels = @json($fareOccupancies);
    const options = () => '<option value="">{{ __('Package currency') }}</option>' + currencies.map(c => `<option value="${c.id}">${c.code}</option>`).join('');
    const prices = (a, s) => Object.entries(occupancyLabels).map(([key, label], i) => `<div class="col-md-4"><label class="form-label small">${label}</label><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="hidden" name="tour_package_accommodations[${a}][seasons][${s}][items][${i}][occupancy_type]" value="${key}"><input type="hidden" name="tour_package_accommodations[${a}][seasons][${s}][items][${i}][label][en]" value="${label}"><input type="number" min="0" step="0.01" class="form-control" name="tour_package_accommodations[${a}][seasons][${s}][items][${i}][price]" placeholder="{{ __('Empty = hidden') }}"></div></div>`).join('');
    const period = (a, s) => `<div class="repeat-box p-3 mb-3" data-fare-period data-index="${s}"><div class="row g-2 align-items-end"><div class="col-lg-3"><label class="form-label small">{{ __('Months / period') }}</label><input class="form-control form-control-sm" name="tour_package_accommodations[${a}][seasons][${s}][period]" placeholder="May to August"></div><div class="col-lg-2"><label class="form-label small">{{ __('Optional title') }}</label><input class="form-control form-control-sm" name="tour_package_accommodations[${a}][seasons][${s}][name][en]" placeholder="Season"></div><div class="col-lg-2"><label class="form-label small">{{ __('From date') }}</label><input type="date" class="form-control form-control-sm" name="tour_package_accommodations[${a}][seasons][${s}][date_from]"></div><div class="col-lg-2"><label class="form-label small">{{ __('To date') }}</label><input type="date" class="form-control form-control-sm" name="tour_package_accommodations[${a}][seasons][${s}][date_to]"></div><div class="col-lg-2"><label class="form-label small">{{ __('Currency') }}</label><select class="form-select form-select-sm" name="tour_package_accommodations[${a}][seasons][${s}][currency_id]">${options()}</select></div><div class="col-lg-1"><button type="button" class="btn btn-sm btn-outline-danger w-100" data-fare-remove>&times;</button></div></div><div class="row g-2 mt-2">${prices(a, s)}</div></div>`;
    const tier = a => `<div class="repeat-box p-3 mb-4" data-fare-tier data-index="${a}"><div class="d-flex gap-2 mb-3"><input class="form-control fw-bold" name="tour_package_accommodations[${a}][name]" placeholder="{{ __('Accommodation level') }}"><button type="button" class="btn btn-outline-danger" data-fare-remove>{{ __('Delete') }}</button></div><div data-fare-periods></div><button type="button" class="btn btn-sm btn-outline-primary" data-fare-add-period>+ {{ __('Month Period') }}</button></div>`;

    root.addEventListener('click', event => {
        const addTier = event.target.closest('[data-fare-add-tier]');
        if (addTier) {
            const list = root.querySelector('[data-fare-tiers]');
            const index = Math.max(-1, ...[...list.children].map(node => Number(node.dataset.index))) + 1;
            list.insertAdjacentHTML('beforeend', tier(index));
            return;
        }
        const addPeriod = event.target.closest('[data-fare-add-period]');
        if (addPeriod) {
            const parent = addPeriod.closest('[data-fare-tier]');
            const list = parent.querySelector('[data-fare-periods]');
            const index = Math.max(-1, ...[...list.children].map(node => Number(node.dataset.index))) + 1;
            list.insertAdjacentHTML('beforeend', period(Number(parent.dataset.index), index));
            return;
        }
        const remove = event.target.closest('[data-fare-remove]');
        if (remove) remove.closest('[data-fare-period], [data-fare-tier]')?.remove();
    });
});
</script>
@endonce
