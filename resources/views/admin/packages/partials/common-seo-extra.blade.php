@php
    $seoPackage = $package ?? null;
    $seoLegacyNile = $seoPackage?->package_type === 'nile_cruise' ? $seoPackage?->nileCruiseDetail : null;
    $seoFocus = $seoPackage?->focus_keyword ?: $seoLegacyNile?->focus_keyword;
    $seoKeywords = (array)($seoPackage?->meta_keywords ?: ($seoLegacyNile?->meta_keywords ?? []));
    $seoOgTitle = $seoPackage?->og_title ?: $seoLegacyNile?->og_title;
    $seoOgDescription = $seoPackage?->og_description ?: $seoLegacyNile?->og_description;
    $seoTwitterCard = $seoPackage?->twitter_card ?: ($seoLegacyNile?->twitter_card ?: 'summary_large_image');
    $seoTwitterTitle = $seoPackage?->twitter_title ?: $seoLegacyNile?->twitter_title;
    $seoTwitterDescription = $seoPackage?->twitter_description ?: $seoLegacyNile?->twitter_description;
    $seoRobotsIndex = $seoPackage?->robots_index ?? $seoLegacyNile?->robots_index ?? true;
    $seoRobotsFollow = $seoPackage?->robots_follow ?? $seoLegacyNile?->robots_follow ?? true;
@endphp
<div class="form-section-card" data-common-tour-section>
    <div class="section-header">
        <div class="section-icon"><i class="ti ti-world-search"></i></div>
        <div><h3>{{ admin_t('Advanced SEO & Social') }}</h3><p>{{ admin_t('Shared Google, Open Graph, Twitter and robots settings for all three Tour Types.') }}</p></div>
    </div>
    <div class="section-body">
        <div class="fields-grid two-up">
            <div><label class="form-label">{{ admin_t('Focus Keyword') }}</label><input class="form-control" name="experience[focus_keyword]" value="{{ old('experience.focus_keyword',$seoFocus) }}"></div>
            <div><label class="form-label">{{ admin_t('Meta Keywords') }}</label><input class="form-control" name="experience[meta_keywords]" value="{{ old('experience.meta_keywords',implode(', ',$seoKeywords)) }}"></div>
            <div><label class="form-label">{{ admin_t('OG Title') }}</label><input class="form-control" name="experience[og_title]" value="{{ old('experience.og_title',$seoOgTitle) }}"></div>
            <div><label class="form-label">{{ admin_t('OG Description') }}</label><textarea class="form-control" rows="3" name="experience[og_description]">{{ old('experience.og_description',$seoOgDescription) }}</textarea></div>
            <div><label class="form-label">{{ admin_t('OG Image') }}</label><input type="file" class="form-control" name="experience[og_image]" accept="image/jpeg,image/png,image/webp">@if($seoPackage?->og_image_path)<img src="{{ asset('storage/'.ltrim($seoPackage->og_image_path,'/')) }}" style="max-width:180px" class="mt-2 rounded"><label class="d-block mt-2"><input type="checkbox" name="experience[remove_og_image]" value="1"> {{ admin_t('Remove OG image') }}</label>@endif</div>
            <div><label class="form-label">{{ admin_t('Twitter Card') }}</label><select class="form-select" name="experience[twitter_card]">@php $tw=old('experience.twitter_card',$seoTwitterCard); @endphp<option value="summary" {{ $tw==='summary'?'selected':'' }}>Summary</option><option value="summary_large_image" {{ $tw==='summary_large_image'?'selected':'' }}>Summary Large Image</option></select></div>
            <div><label class="form-label">{{ admin_t('Twitter Title') }}</label><input class="form-control" name="experience[twitter_title]" value="{{ old('experience.twitter_title',$seoTwitterTitle) }}"></div>
            <div><label class="form-label">{{ admin_t('Twitter Description') }}</label><textarea class="form-control" rows="3" name="experience[twitter_description]">{{ old('experience.twitter_description',$seoTwitterDescription) }}</textarea></div>
            <div class="field-span-2 d-flex flex-wrap gap-4"><input type="hidden" name="experience[robots_index]" value="0"><label><input type="checkbox" name="experience[robots_index]" value="1" {{ old('experience.robots_index',$seoRobotsIndex)?'checked':'' }}> {{ admin_t('Allow Search Engine Indexing') }}</label><input type="hidden" name="experience[robots_follow]" value="0"><label><input type="checkbox" name="experience[robots_follow]" value="1" {{ old('experience.robots_follow',$seoRobotsFollow)?'checked':'' }}> {{ admin_t('Allow Search Engine Follow') }}</label></div>
        </div>
    </div>
</div>
