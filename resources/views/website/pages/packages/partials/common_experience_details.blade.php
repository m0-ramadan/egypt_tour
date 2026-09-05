@if($package->package_type !== 'nile_cruise')
    @if(($whatToBring ?? collect())->isNotEmpty())
        <section class="content-section" id="what-to-bring">
            <h2 class="section-header">{{ __('What to Bring') }}</h2>
            <div class="styled-list"><ul>@foreach($whatToBring as $item)<li>{{ $item }}</li>@endforeach</ul></div>
        </section>
    @endif

    @if(($addons ?? collect())->isNotEmpty())
        <section class="content-section" id="optional-addons">
            <h2 class="section-header">{{ __('Optional Add-ons') }}</h2>
            <div class="row g-3">
                @foreach($addons as $addon)
                    <div class="col-md-6">
                        <div class="included-box h-100">
                            <h4 class="box-title">{{ $addon->title ?? $addon->name ?? __('Add-on') }}</h4>
                            @if($addon->description)<p>{{ $addon->description }}</p>@endif
                            @if($addon->price !== null)
                                <strong>{{ $addon->currency?->symbol ?: ($package->currency?->symbol ?: '$') }}{{ number_format((float)$addon->price, 2) }}</strong>
                                @if($addon->price_unit)<small class="price-meta"> / {{ __(\Illuminate\Support\Str::headline((string)$addon->price_unit)) }}</small>@endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(($promotionalVideos ?? collect())->isNotEmpty() || $brochureUrl)
        <section class="content-section" id="trip-media-resources">
            <h2 class="section-header">{{ __('Trip Media & Brochure') }}</h2>
            <div class="d-flex flex-wrap gap-2">
                @if($brochureUrl)
                    <a class="gold-btn" href="{{ $brochureUrl }}" target="_blank" rel="noopener"><i class="la la-file-pdf"></i> {{ __('Download Trip Brochure') }}</a>
                @endif
                @foreach($promotionalVideos as $video)
                    <a class="gold-btn" href="{{ $video }}" target="_blank" rel="noopener noreferrer"><i class="la la-play-circle"></i> {{ __('Watch Video') }}</a>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($sharedDepositPolicy) && $sharedDepositPolicy !== 'inherit')
        <section class="content-section" id="deposit-policy">
            <h2 class="section-header">{{ __('Booking Deposit') }}</h2>
            <p>
                @if($sharedDepositPolicy === 'not_required')
                    {{ __('No deposit is required for this tour.') }}
                @else
                    {{ __('A deposit is required to confirm this booking.') }}
                    @if($package->deposit_value !== null)
                        <strong>{{ rtrim(rtrim(number_format((float)$package->deposit_value, 2), '0'), '.') }}{{ $package->deposit_type === 'percent' ? '%' : ' '.($package->currency?->code ?? '') }}</strong>
                    @endif
                @endif
            </p>
        </section>
    @endif
@endif
