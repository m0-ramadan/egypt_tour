@php
    $currentPackage = $package ?? null;
    $selectedPackageType = old('package_type', $currentPackage?->package_type ?? '');
    $canonicalTourTypes = [
        'day_tour' => [
            'title' => 'Day Trip',
            'description' => 'Single-day excursion or short activity',
            'icon' => '🗺️',
            'class' => 'day-trip',
        ],
        'travel_package' => [
            'title' => 'Tour Package',
            'description' => 'Multi-day tour with accommodations & itinerary',
            'icon' => '📦',
            'class' => 'tour-package',
        ],
        'nile_cruise' => [
            'title' => 'Nile Cruise',
            'description' => 'Luxury Nile River cruise with cabin options',
            'icon' => '🚢',
            'class' => 'nile-cruise',
        ],
    ];
    $legacyType = $selectedPackageType && !array_key_exists($selectedPackageType, $canonicalTourTypes)
        ? $selectedPackageType
        : null;
@endphp

<div class="tour-type-selector-shell" data-tour-type-selector>
    <div class="tour-type-selector-heading">
        <div>
            <label class="form-label mb-1">{{ admin_t('Tour Type') }} <span class="text-danger">*</span></label>
            <p class="tour-type-selector-copy mb-0">{{ admin_t('Select your tour type — this determines duration format, itinerary structure, and pricing options.') }}</p>
        </div>
    </div>

    <select id="package_type" name="package_type" class="visually-hidden @error('package_type') is-invalid @enderror" data-required-step="1" aria-label="Tour Type">
        <option value="">{{ admin_t('Select Tour Type') }}</option>
        @foreach($canonicalTourTypes as $value => $meta)
            <option value="{{ $value }}" {{ $selectedPackageType === $value ? 'selected' : '' }}>{{ $meta['title'] }}</option>
        @endforeach
        @if($legacyType)
            <option value="{{ $legacyType }}" selected>{{ admin_t('Legacy: :type', ['type' => str_replace('_', ' ', $legacyType)]) }}</option>
        @endif
    </select>

    <div class="tour-type-card-grid" role="radiogroup" aria-label="Tour Type">
        @foreach($canonicalTourTypes as $value => $meta)
            <button type="button"
                class="tour-type-card tour-type-card--{{ $meta['class'] }} {{ $selectedPackageType === $value ? 'is-selected' : '' }}"
                data-tour-type-card="{{ $value }}"
                role="radio"
                aria-checked="{{ $selectedPackageType === $value ? 'true' : 'false' }}">
                <span class="tour-type-card-icon" aria-hidden="true">{{ $selectedPackageType === $value ? '✓' : $meta['icon'] }}</span>
                <span class="tour-type-card-copy">
                    <strong>{{ $meta['icon'] }} {{ $meta['title'] }}</strong>
                    <small>{{ $meta['description'] }}</small>
                </span>
            </button>
        @endforeach
    </div>

    @if($legacyType)
        <div class="alert alert-warning mt-3 mb-0" data-legacy-tour-type-note>
            {{ admin_t('This existing record uses a legacy package type (:type). It is preserved until you explicitly choose one of the three canonical Tour Types above.', ['type' => $legacyType]) }}
        </div>
    @endif

    @error('package_type')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
<style>
            .tour-type-selector-shell{width:100%}.tour-type-selector-copy{opacity:.7;font-size:13px}.tour-type-card-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:14px}.tour-type-card{appearance:none;width:100%;min-height:82px;text-align:left;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.035);color:inherit;border-radius:14px;padding:16px;display:flex;align-items:center;gap:13px;transition:.18s ease;cursor:pointer}.tour-type-card:hover{transform:translateY(-1px);border-color:rgba(255,255,255,.3)}.tour-type-card-icon{width:44px;height:44px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:21px;background:rgba(255,255,255,.08);flex:0 0 44px}.tour-type-card-copy{display:flex;flex-direction:column;gap:3px;min-width:0}.tour-type-card-copy strong{font-size:14px}.tour-type-card-copy small{opacity:.6;font-size:12px;line-height:1.45}.tour-type-card--day-trip.is-selected{border:2px solid #00d084;background:rgba(0,208,132,.13)}.tour-type-card--day-trip.is-selected .tour-type-card-icon{background:#00bd73;color:white}.tour-type-card--tour-package.is-selected{border:2px solid #3b82f6;background:rgba(59,130,246,.13)}.tour-type-card--tour-package.is-selected .tour-type-card-icon{background:#2563eb;color:white}.tour-type-card--nile-cruise.is-selected{border:2px solid #a855f7;background:rgba(168,85,247,.13)}.tour-type-card--nile-cruise.is-selected .tour-type-card-icon{background:#7e22ce;color:white}@media(max-width:900px){.tour-type-card-grid{grid-template-columns:1fr}}
</style>
@endonce

@once
<script>
            document.addEventListener('DOMContentLoaded', function () {
                const select = document.getElementById('package_type');
                const cards = Array.from(document.querySelectorAll('[data-tour-type-card]'));
                if (!select || !cards.length) return;

                function syncTourTypeCards() {
                    const type = String(select.value || '');
                    cards.forEach(card => {
                        const selected = String(card.dataset.tourTypeCard) === type;
                        card.classList.toggle('is-selected', selected);
                        card.setAttribute('aria-checked', selected ? 'true' : 'false');
                        const icon = card.querySelector('.tour-type-card-icon');
                        if (icon) {
                            const metaIcon = card.dataset.tourTypeCard === 'day_tour' ? '🗺️' : (card.dataset.tourTypeCard === 'travel_package' ? '📦' : '🚢');
                            icon.textContent = selected ? '✓' : metaIcon;
                        }
                    });

                    document.querySelectorAll('[data-tour-type-section]').forEach(section => {
                        const allowed = String(section.dataset.tourTypeSection || '').split(',').map(v => v.trim()).filter(Boolean);
                        const active = allowed.includes(type);
                        section.style.display = active ? '' : 'none';
                        section.querySelectorAll('input,select,textarea,button').forEach(el => {
                            if (!active) {
                                if (!el.dataset.tourTypeDisabled) {
                                    el.dataset.tourTypeDisabled = el.disabled ? 'already' : 'conditional';
                                }
                                el.disabled = true;
                            } else {
                                el.disabled = false;
                                delete el.dataset.tourTypeDisabled;
                            }
                        });
                    });

                    const durationRadios = Array.from(document.querySelectorAll('input[name="duration_type"]'));
                    const targetDuration = type === 'day_tour' ? 'hours' : (type === 'travel_package' ? 'days' : null);
                    if (targetDuration) {
                        const radio = durationRadios.find(r => r.value === targetDuration);
                        if (radio && !radio.checked) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                    if (durationRadios.length) {
                        const wrapper = durationRadios[0].closest('.mb-4, .col-12.mb-3');
                        if (wrapper && ['day_tour','travel_package','nile_cruise'].includes(type)) {
                            wrapper.style.display = 'none';
                        } else if (wrapper) {
                            wrapper.style.display = '';
                        }
                    }
                }

                cards.forEach(card => card.addEventListener('click', function () {
                    select.value = this.dataset.tourTypeCard;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    document.querySelector('[data-legacy-tour-type-note]')?.remove();
                    syncTourTypeCards();
                }));

                select.addEventListener('change', syncTourTypeCards);
                syncTourTypeCards();
            });
</script>
@endonce
