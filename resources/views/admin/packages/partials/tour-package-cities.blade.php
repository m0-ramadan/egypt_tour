@php
    $citiesPackage=$package??null;
    $selectedTourCities=collect(old('tour_city_ids',$citiesPackage?->cities?->pluck('id')->all()??[]))->map(fn($id)=>(int)$id)->all();
@endphp
<div class="tour-type-conditional" data-tour-type-section="travel_package">
    <label class="form-label">{{ admin_t('Tour Package Cities') }}</label>
    <select name="tour_city_ids[]" class="form-select" multiple size="6">
        @foreach($destinations??collect() as $city)
            <option value="{{ $city->id }}" {{ in_array((int)$city->id,$selectedTourCities,true)?'selected':'' }}>{{ adminTrans($city->name) }}</option>
        @endforeach
    </select>
    <small class="text-muted">{{ admin_t('Select every Egyptian city visited by this Tour Package. The Primary City remains the start city.') }}</small>
</div>
