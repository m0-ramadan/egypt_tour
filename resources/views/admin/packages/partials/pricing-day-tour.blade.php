<div class="pricing-type-block" id="dayTourPricingBlock" data-pricing-type="day_tour">
    <div class="card mb-4 border-light bg-light p-3 w-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold mb-1 text-primary"><i class="la la-users me-1"></i> {{ __('Group-Size Pricing Tiers') }}</h6>
                <small class="text-muted">{{ __('Set price per person based on group size (Day Trips)') }}</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnLoadDefaultGroupTiers">
                <i class="ti ti-refresh"></i> {{ __('Load Defaults') }}
            </button>
        </div>

        <div id="groupTiersWrapper" class="stack-list">
            @php
                $tiers = old('experience.group_pricing_tiers', isset($package) ? ($package->group_pricing_tiers ?? []) : []);
            @endphp
            @foreach ((array)$tiers as $tierIndex => $tier)
                <div class="repeat-box group-tier-row mb-3 p-3 border rounded bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary text-white">{{ __('Tier #') }}<span class="tier-number">{{ $tierIndex + 1 }}</span></span>
                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-tier"><i class="ti ti-trash"></i> {{ __('Delete') }}</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">{{ __('Tier Title') }}</label>
                            <input type="text" name="experience[group_pricing_tiers][{{ $tierIndex }}][title]" class="form-control form-control-sm" value="{{ $tier['title'] ?? ($tier['label'] ?? '') }}" placeholder="e.g. Couple's Journey">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">{{ __('Min Persons') }}</label>
                            <input type="number" min="1" name="experience[group_pricing_tiers][{{ $tierIndex }}][min]" class="form-control form-control-sm" value="{{ $tier['min'] ?? ($tier['persons_count'] ?? 1) }}" placeholder="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">{{ __('Max Persons (Optional)') }}</label>
                            <input type="number" min="1" name="experience[group_pricing_tiers][{{ $tierIndex }}][max]" class="form-control form-control-sm" value="{{ $tier['max'] ?? '' }}" placeholder="Max pax">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">{{ __('Price Per Person ($)') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="experience[group_pricing_tiers][{{ $tierIndex }}][price_per_person]" class="form-control" value="{{ $tier['price_per_person'] ?? '' }}" placeholder="150.00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">{{ __('Badge Label') }}</label>
                            <input type="text" name="experience[group_pricing_tiers][{{ $tierIndex }}][badge_label]" class="form-control form-control-sm" value="{{ $tier['badge_label'] ?? ($tier['badge'] ?? '') }}" placeholder="e.g. Most Popular">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn dynamic-add-btn mt-2" id="btnAddGroupTier">
            <span class="btn-icon-text">
                <i class="ti ti-plus"></i>
                <span>{{ __('Add New Tier') }}</span>
            </span>
        </button>
    </div>
</div>

<template id="groupTierTemplate">
    <div class="repeat-box group-tier-row mb-3 p-3 border rounded bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-primary text-white">{{ __('Tier #') }}<span class="tier-number">__INDEX_PLUS_1__</span></span>
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-tier"><i class="ti ti-trash"></i> {{ __('Delete') }}</button>
        </div>
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small fw-bold">{{ __('Tier Title') }}</label>
                <input type="text" name="experience[group_pricing_tiers][__INDEX__][title]" class="form-control form-control-sm" placeholder="e.g. Small Group">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">{{ __('Min Persons') }}</label>
                <input type="number" min="1" name="experience[group_pricing_tiers][__INDEX__][min]" class="form-control form-control-sm" value="1" placeholder="1">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">{{ __('Max Persons (Optional)') }}</label>
                <input type="number" min="1" name="experience[group_pricing_tiers][__INDEX__][max]" class="form-control form-control-sm" placeholder="Max pax">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">{{ __('Price Per Person ($)') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="experience[group_pricing_tiers][__INDEX__][price_per_person]" class="form-control" placeholder="150.00">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">{{ __('Badge Label') }}</label>
                <input type="text" name="experience[group_pricing_tiers][__INDEX__][badge_label]" class="form-control form-control-sm" placeholder="e.g. Best Value">
            </div>
        </div>
    </div>
</template>
