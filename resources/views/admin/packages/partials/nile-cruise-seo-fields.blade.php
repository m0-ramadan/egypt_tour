@php
    $ncSeoPackage = $package ?? null;
    $ncSeo = $ncSeoPackage?->nileCruiseDetail;
    $ncMetaKeywords = old('nile_cruise.meta_keywords', implode(', ', (array) ($ncSeo?->meta_keywords ?? [])));
    $ncRobotsIndex = (bool) old('nile_cruise.robots_index', $ncSeo?->robots_index ?? true);
    $ncRobotsFollow = (bool) old('nile_cruise.robots_follow', $ncSeo?->robots_follow ?? true);
@endphp

<div class="nile-cruise-seo-advanced border rounded-3 p-3 mt-3" data-nile-seo-only style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1"><i class="ti ti-ship me-1"></i>{{ admin_t('Nile Cruise SEO & Social') }}</h5>
            <p class="text-muted small mb-0">{{ admin_t('Optional — customizes Nile Cruise display in search and social sharing, leaving general SEO fields as default.') }}</p>
        </div>
        <span class="badge bg-info-subtle text-info">{{ admin_t('Nile Cruise only') }}</span>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Focus Keyword') }}</label>
            <input type="text" class="form-control" name="nile_cruise[focus_keyword]"
                value="{{ old('nile_cruise.focus_keyword', $ncSeo?->focus_keyword) }}"
                placeholder="e.g. Luxury Dahabiya Nile Cruise">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Meta Keywords') }}</label>
            <input type="text" class="form-control" name="nile_cruise[meta_keywords]"
                value="{{ $ncMetaKeywords }}"
                placeholder="Dahabiya, Nile cruise, Luxor, Aswan">
            <div class="form-text">{{ admin_t('Separate keywords with commas.') }}</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Open Graph Title') }}</label>
            <input type="text" class="form-control" name="nile_cruise[og_title]"
                value="{{ old('nile_cruise.og_title', $ncSeo?->og_title) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Twitter Title') }}</label>
            <input type="text" class="form-control" name="nile_cruise[twitter_title]"
                value="{{ old('nile_cruise.twitter_title', $ncSeo?->twitter_title) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Open Graph Description') }}</label>
            <textarea class="form-control" rows="3" name="nile_cruise[og_description]">{{ old('nile_cruise.og_description', $ncSeo?->og_description) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Twitter Description') }}</label>
            <textarea class="form-control" rows="3" name="nile_cruise[twitter_description]">{{ old('nile_cruise.twitter_description', $ncSeo?->twitter_description) }}</textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Social Share Image') }}</label>
            @if($ncSeo?->social_image_path)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . ltrim($ncSeo->social_image_path, '/')) }}" alt="" style="max-width:180px;max-height:100px;object-fit:cover;border-radius:10px;">
                </div>
                <input type="hidden" name="nile_cruise[remove_social_image]" value="0">
                <label class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="nile_cruise[remove_social_image]" value="1">
                    <span class="form-check-label">{{ admin_t('Remove current social share image') }}</span>
                </label>
            @endif
            <input type="file" class="form-control" name="nile_cruise[social_image]" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">{{ admin_t('If left empty, the main trip image will be used.') }}</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ admin_t('Twitter Card') }}</label>
            <select class="form-select" name="nile_cruise[twitter_card]">
                @php $ncTwitterCard = old('nile_cruise.twitter_card', $ncSeo?->twitter_card ?: 'summary_large_image'); @endphp
                <option value="summary_large_image" {{ $ncTwitterCard === 'summary_large_image' ? 'selected' : '' }}>summary_large_image</option>
                <option value="summary" {{ $ncTwitterCard === 'summary' ? 'selected' : '' }}>summary</option>
            </select>
        </div>

        <div class="col-md-6">
            <input type="hidden" name="nile_cruise[robots_index]" value="0">
            <label class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="nile_cruise[robots_index]" value="1" {{ $ncRobotsIndex ? 'checked' : '' }}>
                <span class="form-check-label">{{ admin_t('Allow search engines to index this Nile Cruise') }}</span>
            </label>
        </div>
        <div class="col-md-6">
            <input type="hidden" name="nile_cruise[robots_follow]" value="0">
            <label class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="nile_cruise[robots_follow]" value="1" {{ $ncRobotsFollow ? 'checked' : '' }}>
                <span class="form-check-label">{{ admin_t('Allow search engines to follow links') }}</span>
            </label>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    const block = document.querySelector('[data-nile-seo-only]');
    const packageType = document.querySelector('[name="package_type"]');
    if (!block || !packageType) return;
    const sync = () => {
        const active = packageType.value === 'nile_cruise';
        block.style.display = active ? '' : 'none';
        block.querySelectorAll('input,select,textarea,button').forEach(el => el.disabled = !active);
    };
    packageType.addEventListener('change', sync);
    sync();
});
</script>
@endonce
