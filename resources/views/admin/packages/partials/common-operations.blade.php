@php
    $opsPackage = $package ?? null;
    $opsLegacyNile = $opsPackage?->package_type === 'nile_cruise' ? $opsPackage?->nileCruiseDetail : null;
    $opsDaysSource = (array)($opsPackage?->operating_days ?: ($opsLegacyNile?->operating_days ?? []));
    $opsDays = collect(old('experience.operating_days',$opsDaysSource))->map(fn($v)=>strtolower((string)$v))->all();
    $opsAddons = old('experience.addons');
    if (!is_array($opsAddons)) {
        $opsAddons = $opsPackage?->addons?->map(fn($a)=>[
            'title'=>$a->title,'description'=>$a->description,'price'=>$a->price,'currency_id'=>$a->currency_id,'price_unit'=>$a->price_unit,'is_active'=>$a->is_active?1:0,
        ])->values()->all() ?? [];
        if ($opsAddons === [] && $opsPackage?->package_type === 'nile_cruise') {
            $opsAddons = $opsPackage?->nileCruiseAddons?->map(fn($a)=>[
                'title'=>$a->name,'description'=>$a->description,'price'=>$a->price,'currency_id'=>$a->currency_id,'price_unit'=>'per person','is_active'=>$a->is_active?1:0,
            ])->values()->all() ?? [];
        }
    }
    $allowedPaymentSource = (array)($opsPackage?->allowed_payment_method_ids ?: ($opsLegacyNile?->allowed_payment_method_ids ?? []));
    $allowedPaymentIds = collect(old('experience.allowed_payment_method_ids',$allowedPaymentSource))->map(fn($v)=>(int)$v)->all();
@endphp
<div class="form-section-card" data-common-tour-section>
    <div class="section-header">
        <div class="section-icon"><i class="ti ti-calendar-time"></i></div>
        <div>
            <h3>{{ admin_t('Availability & Scheduling') }}</h3>
            <p>{{ admin_t('Operating days, capacity and timezone used by availability and booking.') }}</p>
        </div>
    </div>
    <div class="section-body">
        <div class="fields-grid two-up">
            <div class="field-span-2">
                <label class="form-label">{{ admin_t('Operating days') }} *</label>
                <div class="d-flex flex-wrap gap-3 mt-2">
                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                        <label class="d-flex align-items-center gap-2"><input type="checkbox" name="experience[operating_days][]" value="{{ $day }}" {{ in_array($day,$opsDays,true)?'checked':'' }}> {{ ucfirst($day) }}</label>
                    @endforeach
                </div>
            </div>
            <div class="tour-type-conditional" data-tour-type-section="day_tour">
                <label class="form-label">{{ admin_t('Daily departure times') }}</label>
                <textarea class="form-control" rows="4" name="experience[departure_times]" placeholder="07:00&#10;08:00&#10;14:00">{{ old('experience.departure_times',implode("\n",(array)($opsPackage?->departure_times ?? []))) }}</textarea>
                <small class="text-muted">{{ admin_t('Day Trip only. One time per line.') }}</small>
            </div>
            <div>
                <label class="form-label">{{ admin_t('Default seat capacity') }}</label>
                <input type="number" min="1" class="form-control" name="experience[default_seat_capacity]" value="{{ old('experience.default_seat_capacity',$opsPackage?->default_seat_capacity) }}">
            </div>
            <div>
                <label class="form-label">{{ admin_t('Tour timezone') }}</label>
                <input type="text" class="form-control" name="experience[tour_timezone]" value="{{ old('experience.tour_timezone', $opsPackage?->tour_timezone ?? ($opsLegacyNile?->timezone ?? 'Africa/Cairo')) }}" placeholder="Africa/Cairo">
            </div>
        </div>
    </div>
</div>

<div class="form-section-card" data-common-tour-section>
    <div class="section-header">
        <div class="section-icon"><i class="ti ti-shopping-cart-plus"></i></div>
        <div><h3>{{ admin_t('Optional Add-ons') }}</h3><p>{{ admin_t('Paid or free extras customers can select at checkout.') }}</p></div>
    </div>
    <div class="section-body">
        <div id="packageAddonsList">
            @foreach($opsAddons as $i=>$addon)
                <div class="repeat-box package-addon-row mb-3" data-package-addon>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label">{{ admin_t('Title') }}</label><input class="form-control" name="experience[addons][{{ $i }}][title]" value="{{ $addon['title']??'' }}"></div>
                        <div class="col-md-3"><label class="form-label">{{ admin_t('Description') }}</label><input class="form-control" name="experience[addons][{{ $i }}][description]" value="{{ $addon['description']??'' }}"></div>
                        <div class="col-md-2"><label class="form-label">{{ admin_t('Price') }}</label><input type="number" min="0" step="0.01" class="form-control" name="experience[addons][{{ $i }}][price]" value="{{ $addon['price']??'' }}"></div>
                        <div class="col-md-2"><label class="form-label">{{ admin_t('Currency') }}</label><select class="form-select" name="experience[addons][{{ $i }}][currency_id]"><option value="">-</option>@foreach($currencies??collect() as $currency)<option value="{{ $currency->id }}" {{ (string)($addon['currency_id']??'')===(string)$currency->id?'selected':'' }}>{{ $currency->code }}</option>@endforeach</select></div>
                        <div class="col-md-2"><label class="form-label">{{ admin_t('Price unit') }}</label><input class="form-control" name="experience[addons][{{ $i }}][price_unit]" value="{{ $addon['price_unit']??'' }}" placeholder="per person"></div>
                        <div class="col-12 d-flex align-items-center justify-content-between"><input type="hidden" name="experience[addons][{{ $i }}][is_active]" value="0"><label><input type="checkbox" name="experience[addons][{{ $i }}][is_active]" value="1" {{ !array_key_exists('is_active',$addon)||!empty($addon['is_active'])?'checked':'' }}> {{ admin_t('Active') }}</label><button type="button" class="btn btn-outline-danger btn-sm" data-remove-package-addon>{{ admin_t('Remove') }}</button></div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-wizard-outline" id="addPackageAddonBtn"><i class="ti ti-plus"></i> {{ admin_t('Add add-on') }}</button>
        <small class="d-block text-muted mt-2">{{ admin_t('Add-ons do not change the base package price. They are added at checkout only.') }}</small>
    </div>
</div>

<div class="form-section-card" data-common-tour-section>
    <div class="section-header">
        <div class="section-icon"><i class="ti ti-shield-check"></i></div>
        <div><h3>{{ admin_t('Booking & Policy Overrides') }}</h3><p>{{ admin_t('Optional per-tour deposit and payment method overrides.') }}</p></div>
    </div>
    <div class="section-body">
        <div class="fields-grid two-up">
            <div><label class="form-label">{{ admin_t('Deposit Policy') }}</label><select class="form-select" name="experience[deposit_policy]">@php $dep=old('experience.deposit_policy', $opsPackage?->deposit_policy ?? ($opsLegacyNile?->deposit_policy ?? 'inherit')); @endphp<option value="inherit" {{ $dep==='inherit'?'selected':'' }}>Inherit</option><option value="required" {{ $dep==='required'?'selected':'' }}>Required</option><option value="not_required" {{ $dep==='not_required'?'selected':'' }}>Not required</option></select></div>
            <div><label class="form-label">{{ admin_t('Deposit Type') }}</label><select class="form-select" name="experience[deposit_type]">@php $dept=old('experience.deposit_type', $opsPackage?->deposit_type ?? $opsLegacyNile?->deposit_type); @endphp<option value="">-</option><option value="percent" {{ $dept==='percent'?'selected':'' }}>Percent</option><option value="fixed" {{ $dept==='fixed'?'selected':'' }}>Fixed</option></select></div>
            <div><label class="form-label">{{ admin_t('Deposit Value') }}</label><input type="number" min="0" step="0.01" class="form-control" name="experience[deposit_value]" value="{{ old('experience.deposit_value',$opsPackage?->deposit_value ?? $opsLegacyNile?->deposit_value) }}"></div>
            <div><label class="form-label">{{ admin_t('Allowed Payment Methods') }}</label><div class="d-flex flex-wrap gap-3 mt-2">@foreach($paymentMethods??collect() as $pm)<label><input type="checkbox" name="experience[allowed_payment_method_ids][]" value="{{ $pm->id }}" {{ in_array((int)$pm->id,$allowedPaymentIds,true)?'checked':'' }}> {{ $pm->name??('Payment #'.$pm->id) }}</label>@endforeach</div></div>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded',function(){
    const list=document.getElementById('packageAddonsList');
    const addBtn=document.getElementById('addPackageAddonBtn');
    if(!list||!addBtn)return;
    let addonIndex=list.querySelectorAll('[data-package-addon]').length;
    const currencies=@json(collect($currencies??[])->map(fn($c)=>['id'=>$c->id,'code'=>$c->code])->values());
    const currencyOptions=()=>'<option value="">-</option>'+currencies.map(c=>`<option value="${c.id}">${c.code}</option>`).join('');
    addBtn.addEventListener('click',function(){
        const i=addonIndex++;
        list.insertAdjacentHTML('beforeend',`<div class="repeat-box package-addon-row mb-3" data-package-addon><div class="row g-2"><div class="col-md-3"><label class="form-label">Title</label><input class="form-control" name="experience[addons][${i}][title]"></div><div class="col-md-3"><label class="form-label">Description</label><input class="form-control" name="experience[addons][${i}][description]"></div><div class="col-md-2"><label class="form-label">Price</label><input type="number" min="0" step="0.01" class="form-control" name="experience[addons][${i}][price]"></div><div class="col-md-2"><label class="form-label">Currency</label><select class="form-select" name="experience[addons][${i}][currency_id]">${currencyOptions()}</select></div><div class="col-md-2"><label class="form-label">Price unit</label><input class="form-control" name="experience[addons][${i}][price_unit]" placeholder="per person"></div><div class="col-12 d-flex align-items-center justify-content-between"><input type="hidden" name="experience[addons][${i}][is_active]" value="0"><label><input type="checkbox" checked name="experience[addons][${i}][is_active]" value="1"> Active</label><button type="button" class="btn btn-outline-danger btn-sm" data-remove-package-addon>Remove</button></div></div></div>`);
    });
    list.addEventListener('click',e=>{const btn=e.target.closest('[data-remove-package-addon]');if(btn)btn.closest('[data-package-addon]')?.remove();});
});
</script>
@endonce
