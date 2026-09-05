@php
    $ncDetail = $package->nileCruiseDetail;
    $ncWhatToBring = isset($whatToBring) && $whatToBring->isNotEmpty() ? $whatToBring : collect((array) ($ncDetail?->what_to_bring ?? []));

    $incList = (isset($included) && $included->isNotEmpty())
        ? $included
        : ((isset($inclusions) && !empty($inclusions)) ? collect($inclusions) : collect());

    $excList = (isset($excluded) && $excluded->isNotEmpty())
        ? $excluded
        : ((isset($exclusions) && !empty($exclusions)) ? collect($exclusions) : collect());

    $hasInclusions = $incList->isNotEmpty();
    $hasExclusions = $excList->isNotEmpty();
@endphp

@if($hasInclusions || $hasExclusions || $ncWhatToBring->isNotEmpty())
    <section class="content-section" id="includes-excludes">
        <h2 class="section-header">{{ __('What\'s Included & Excluded') }}</h2>
        <div class="row g-4">
            @if($hasInclusions)
                <div class="{{ $hasExclusions ? 'col-md-6' : 'col-12' }}">
                    <div class="included-box h-100">
                        <h4 class="box-title">{{ __('Included in Your Journey') }}</h4>
                        <div class="styled-list">
                            <ul>
                                @foreach($incList as $inc)
                                    <li>{{ is_object($inc) ? ($inc->display_content ?? $inc->title ?? '') : (is_array($inc) ? ($inc['title'] ?? '') : $inc) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if($hasExclusions)
                <div class="{{ $hasInclusions ? 'col-md-6' : 'col-12' }}">
                    <div class="excluded-box h-100">
                        <h4 class="box-title">{{ __('Not Included') }}</h4>
                        <div class="styled-list">
                            <ul>
                                @foreach($excList as $exc)
                                    <li>{{ is_object($exc) ? ($exc->display_content ?? $exc->title ?? '') : (is_array($exc) ? ($exc['title'] ?? '') : $exc) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($ncWhatToBring->isNotEmpty())
            <div class="mt-4 pt-3">
                <h4 class="box-title fw-bold mb-3" style="color: var(--primary-navy, #1c325c); font-family: 'Playfair Display', serif;">
                    <i class="la la-suitcase me-2" style="color: var(--rich-gold, #c5955b);"></i>{{ __('What to Bring') }}
                </h4>
                <div class="styled-list">
                    <ul>
                        @foreach($ncWhatToBring as $bringItem)
                            <li>{{ $bringItem }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </section>
@endif
