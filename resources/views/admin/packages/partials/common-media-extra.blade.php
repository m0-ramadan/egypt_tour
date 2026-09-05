@php
    $mediaPackage = $package ?? null;
    $mediaLegacyNile = $mediaPackage?->package_type === 'nile_cruise' ? $mediaPackage?->nileCruiseDetail : null;
    $mediaVideos = (array)($mediaPackage?->promotional_videos ?: ($mediaLegacyNile?->promotional_videos ?? []));
@endphp
<div class="form-section-card" data-common-tour-section>
    <div class="section-header">
        <div class="section-icon"><i class="ti ti-photo-video"></i></div>
        <div>
            <h3>{{ admin_t('Brochure & Promotional Videos') }}</h3>
            <p>{{ admin_t('Optional media shown on the public tour detail page.') }}</p>
        </div>
    </div>
    <div class="section-body">
        <div class="fields-grid two-up">
            <div>
                <label class="form-label">{{ admin_t('Trip Brochure (PDF)') }}</label>
                <input type="file" class="form-control" name="experience[brochure]" accept="application/pdf">
                @if($mediaPackage?->brochure_path)
                    <div class="mt-2"><a href="{{ asset('storage/'.ltrim($mediaPackage->brochure_path,'/')) }}" target="_blank">{{ admin_t('Open current brochure') }}</a></div>
                    <label class="mt-2 d-flex gap-2 align-items-center"><input type="checkbox" name="experience[remove_brochure]" value="1"> {{ admin_t('Remove brochure') }}</label>
                @endif
            </div>
            <div>
                <label class="form-label">{{ admin_t('Promotional Videos') }}</label>
                <textarea class="form-control" rows="5" name="experience[promotional_videos]" placeholder="https://youtube.com/...&#10;https://vimeo.com/...">{{ old('experience.promotional_videos', implode("\n", $mediaVideos)) }}</textarea>
                <small class="text-muted">{{ admin_t('One video URL per line.') }}</small>
            </div>
        </div>
    </div>
</div>
