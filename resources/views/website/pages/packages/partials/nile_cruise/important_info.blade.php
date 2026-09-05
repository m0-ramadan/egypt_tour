@php
    $ncDetail = $package->nileCruiseDetail;
    $ncVideos = isset($promotionalVideos) && $promotionalVideos->isNotEmpty() ? $promotionalVideos : collect((array) ($ncDetail?->promotional_videos ?? []));
    $ncDepositPolicy = $package->deposit_policy ?: ($ncDetail?->deposit_policy ?? null);
    $ncDepositType = $package->deposit_type ?: ($ncDetail?->deposit_type ?? null);
    $ncDepositValue = $package->deposit_value ?? ($ncDetail?->deposit_value ?? null);
    $childrenPolicy = $package->getTranslation('children_policy');
    $pickupPolicy = $package->getTranslation('pickup_policy');
    $cancellationPolicy = $package->getTranslation('cancellation_policy');
    $termsConditions = $package->getTranslation('terms_conditions');
    $hasPickupDropoff = !empty($ncDetail?->pickup_notes) || !empty($ncDetail?->dropoff_notes) || !empty($pickupPolicy);
    $hasBrochures = !empty($ncDetail?->fact_sheet_path) || !empty($brochureUrl);
    $hasDepositInfo = !empty($ncDepositPolicy) && $ncDepositPolicy !== 'inherit';
    $hasVideos = $ncVideos->isNotEmpty();
    $hasPolicies = $childrenPolicy || $cancellationPolicy || $termsConditions;
    $hasImportantInfo = $hasPickupDropoff || $hasBrochures || $hasDepositInfo || $hasVideos || $hasPolicies;
@endphp

@if($hasImportantInfo)
    <section class="content-section" id="important-information">
        <h2 class="section-header">{{ __('Important Information & Cruise Policies') }}</h2>

        <div class="row g-4">
            @if($hasPickupDropoff)
                <div class="col-md-6">
                    <div class="included-box h-100">
                        <h4 class="box-title">
                            <i class="la la-map-pin me-2" style="color: var(--rich-gold, #c5955b);"></i>{{ __('Pickup & Drop-off') }}
                        </h4>
                        <div class="about-content">
                            @if($ncDetail?->pickup_notes)<div>{{ $ncDetail->pickup_notes }}</div>@endif
                            @if($ncDetail?->dropoff_notes)<div class="mt-1">{{ $ncDetail->dropoff_notes }}</div>@endif
                            @if(!$ncDetail?->pickup_notes && !$ncDetail?->dropoff_notes && $pickupPolicy)<div>{!! $pickupPolicy !!}</div>@endif
                        </div>
                    </div>
                </div>
            @endif

            @if($hasDepositInfo)
                <div class="col-md-6">
                    <div class="included-box h-100">
                        <h4 class="box-title">
                            <i class="la la-file-alt me-2" style="color: var(--rich-gold, #c5955b);"></i>{{ __('Booking & Deposit') }}
                        </h4>
                        <div class="about-content">
                            @if($ncDepositPolicy === 'not_required')
                                {{ __('No deposit is required to confirm this booking.') }}
                            @else
                                {{ __('A deposit is required to guarantee your cabin reservation.') }}
                                @if($ncDepositValue !== null)
                                    <strong>: {{ rtrim(rtrim(number_format((float)$ncDepositValue,2), '0'), '.') }}{{ $ncDepositType === 'percent' ? '%' : ' '.($package->currency?->code ?? '') }}</strong>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($childrenPolicy)
                <div class="col-md-6">
                    <div class="included-box h-100">
                        <h4 class="box-title">
                            <i class="la la-child me-2" style="color: var(--rich-gold, #c5955b);"></i>{{ __('Children Policy') }}
                        </h4>
                        <div class="about-content">{!! $childrenPolicy !!}</div>
                    </div>
                </div>
            @endif

            @if($cancellationPolicy)
                <div class="col-md-6">
                    <div class="included-box h-100">
                        <h4 class="box-title">
                            <i class="la la-info-circle me-2" style="color: var(--rich-gold, #c5955b);"></i>{{ __('Cancellation Policy') }}
                        </h4>
                        <div class="about-content">{!! $cancellationPolicy !!}</div>
                    </div>
                </div>
            @endif

            @if($termsConditions)
                <div class="col-12">
                    <div class="included-box">
                        <h4 class="box-title">
                            <i class="la la-file-alt me-2" style="color: var(--rich-gold, #c5955b);"></i>{{ __('Terms & Conditions') }}
                        </h4>
                        <div class="about-content">{!! $termsConditions !!}</div>
                    </div>
                </div>
            @endif
        </div>

        @if($hasBrochures || $hasVideos)
            <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
                @if($ncDetail?->fact_sheet_path)
                    <a class="gold-btn nc-fact-sheet" href="{{ asset('storage/'.$ncDetail->fact_sheet_path) }}" target="_blank" rel="noopener"><i class="la la-file-alt me-1"></i>{{ __('Fact Sheet') }}</a>
                @endif
                @if(!empty($brochureUrl))
                    <a class="gold-btn nc-fact-sheet" href="{{ $brochureUrl }}" target="_blank" rel="noopener"><i class="la la-file-alt me-1"></i>{{ __('Cruise Brochure') }}</a>
                @endif
                @if($hasVideos)
                    @foreach($ncVideos as $video)
                        <a class="gold-btn" href="{{ $video }}" target="_blank" rel="noopener noreferrer"><i class="la la-eye me-1"></i>{{ __('Watch Cruise Video') }}</a>
                    @endforeach
                @endif
            </div>
        @endif
    </section>
@endif
