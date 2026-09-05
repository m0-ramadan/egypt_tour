@php
    $expPackage = $package ?? null;
    $expLegacyNile = $expPackage?->package_type === 'nile_cruise' ? $expPackage?->nileCruiseDetail : null;
    $expLanguagesSource = (array)($expPackage?->on_tour_languages ?: ($expLegacyNile?->on_tour_languages ?? []));
    $expWhatToBringSource = (array)($expPackage?->what_to_bring ?: ($expLegacyNile?->what_to_bring ?? []));
    $expLanguages = collect(old('experience.on_tour_languages', $expLanguagesSource))->map(fn($v)=>strtolower(trim((string)$v)))->all();
    $expWhatToBring = old('experience.what_to_bring', implode("\n", $expWhatToBringSource));
    $expHighlights = old('highlights');
    if ($expHighlights === null) {
        $expHighlights = $expPackage?->highlights?->map(fn($h)=>$h->display_title ?: $h->display_description)->filter()->implode("\n") ?? '';
    } elseif (is_array($expHighlights)) {
        $expHighlights = collect($expHighlights)->map(fn($h)=>is_array($h)?($h['title']??''):$h)->filter()->implode("\n");
    }
    $expTags = old('tags');
    if ($expTags === null) {
        $expTags = $expPackage?->tags?->map(fn($t)=>$t->display_name)->filter()->implode(', ') ?? '';
    }
    $tpDetail = $expPackage?->tourPackageDetail;
    $tpMeals = collect(old('tour_package.meals_included', (array)($tpDetail?->meals_included ?? [])))->map(fn($v)=>strtolower(trim((string)$v)))->all();
@endphp

<input type="hidden" name="experience[_present]" value="1">

<div class="form-section-card" data-common-tour-section>
    <div class="section-header">
        <div class="section-icon"><i class="ti ti-language"></i></div>
        <div>
            <h3>{{ admin_t('Tour Experience Details') }}</h3>
            <p>{{ admin_t('Shared information used by Day Trip, Tour Package and Nile Cruise.') }}</p>
        </div>
    </div>
    <div class="section-body">
        <div class="fields-grid two-up">
            <div class="field-span-2">
                <label class="form-label">{{ admin_t('On-Tour Languages') }}</label>
                <div class="d-flex flex-wrap gap-3 mt-2">
                    @foreach(['english'=>'English (English)','german'=>'German (Deutsch)','french'=>'French (Français)'] as $code=>$label)
                        <label class="form-check-label d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" name="experience[on_tour_languages][]" value="{{ $code }}" {{ in_array($code,$expLanguages,true)?'checked':'' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <small class="text-muted">{{ admin_t('Languages delivered by the live guide or audio commentary.') }}</small>
            </div>

            <div>
                <label class="form-label">{{ admin_t('Highlights') }}</label>
                <textarea class="form-control" rows="5" name="highlights" placeholder="{{ admin_t('One highlight per line') }}">{{ $expHighlights }}</textarea>
            </div>

            <div>
                <label class="form-label">{{ admin_t('What to Bring') }}</label>
                <textarea class="form-control" rows="5" name="experience[what_to_bring]" placeholder="Comfortable shoes&#10;Sun protection&#10;Passport copy">{{ $expWhatToBring }}</textarea>
            </div>

            <div class="field-span-2">
                <label class="form-label">{{ admin_t('Tags') }} <small class="text-muted">({{ admin_t('optional') }})</small></label>
                <input type="text" class="form-control" name="tags" value="{{ $expTags }}" placeholder="Cultural, Family, Adventure">
                <small class="text-muted">{{ admin_t('Comma-separated. Existing tag names are reused automatically.') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="form-section-card tour-type-conditional" data-tour-type-section="travel_package">
    <input type="hidden" name="tour_package[_present]" value="1">
    <div class="section-header">
        <div class="section-icon"><i class="ti ti-building-community"></i></div>
        <div>
            <h3>{{ admin_t('Tour Package Details') }}</h3>
            <p>{{ admin_t('Accommodation, meals and itinerary flexibility for multi-day packages.') }}</p>
        </div>
    </div>
    <div class="section-body">
        <div class="fields-grid two-up">
            <div>
                <label class="form-label">{{ admin_t('Accommodation Standard') }}</label>
                <select class="form-select" name="tour_package[accommodation_standard]">
                    @php $acc=old('tour_package.accommodation_standard',$tpDetail?->accommodation_standard); @endphp
                    <option value="">{{ admin_t('Not specified') }}</option>
                    @foreach(['no_accommodation'=>'No accommodation','standard'=>'Standard','3_star'=>'3 Star','4_star'=>'4 Star','5_star'=>'5 Star','luxury'=>'Luxury','mixed'=>'Mixed'] as $value=>$label)
                        <option value="{{ $value }}" {{ $acc===$value?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ admin_t('Meals Included') }}</label>
                <div class="d-flex flex-wrap gap-3 mt-2">
                    @foreach(['breakfast'=>'Breakfast','lunch'=>'Lunch','dinner'=>'Dinner'] as $value=>$label)
                        <label class="d-flex align-items-center gap-2"><input type="checkbox" name="tour_package[meals_included][]" value="{{ $value }}" {{ in_array($value,$tpMeals,true)?'checked':'' }}> {{ $label }}</label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="form-label">{{ admin_t('Itinerary Mode') }}</label>
                @php $itineraryMode = old('experience.itinerary_mode', $expPackage?->itinerary_mode ?: 'simple'); @endphp
                <select class="form-select" name="experience[itinerary_mode]" data-tour-package-itinerary-mode>
                    <option value="simple" {{ $itineraryMode === 'simple' ? 'selected' : '' }}>{{ admin_t('Simple — one main summary per day') }}</option>
                    <option value="advanced" {{ $itineraryMode === 'advanced' ? 'selected' : '' }}>{{ admin_t('Advanced — multiple ordered activities per day') }}</option>
                </select>
                <small class="text-muted">{{ admin_t('Advanced mode adds structured activity stops, transport notes and day accommodation without changing the Package parent record.') }}</small>
            </div>
            <div>
                <label class="form-label">{{ admin_t('Package Route Behavior') }}</label>
                <div class="form-control bg-transparent" style="min-height:42px">{{ admin_t('Multi-city within Egypt · first selected city is the primary start city') }}</div>
            </div>

            <div class="field-span-2">
                <input type="hidden" name="tour_package[flexible_itinerary]" value="0">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="tour_package[flexible_itinerary]" value="1" {{ old('tour_package.flexible_itinerary',$tpDetail?->flexible_itinerary)?'checked':'' }}>
                    <strong>{{ admin_t('Flexible Itinerary') }}</strong>
                </label>
                <small class="text-muted">{{ admin_t('Allow customers to request changes to the day-by-day plan.') }}</small>
            </div>
            <div class="field-span-2">
                <label class="form-label">{{ admin_t('Package Notes') }}</label>
                <textarea class="form-control" rows="3" name="tour_package[additional_notes]">{{ old('tour_package.additional_notes',$tpDetail?->additional_notes) }}</textarea>
            </div>
        </div>
    </div>
</div>
