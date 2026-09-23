<div class="pricing-type-block" id="nileCruisePricingBlock" data-pricing-type="nile_cruise">
    <div class="pricing-card-wrapper mb-4 p-3 w-100">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white p-3 rounded-circle d-flex align-items-center justify-content-center"
                style="width: 50px; height: 50px;">
                <i class="la la-ship fs-3"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-primary">{{ __('Nile Cruise Pricing Engine') }}</h6>
                <p class="text-light opacity-75 small mb-0">
                    {{ __('Nile Cruise pricing is managed dynamically based on durations, cabins, and seasonal pricing configured in the Itinerary & Nile Cruise Details step.') }}
                </p>
            </div>
        </div>

        @if (isset($package) && $package->nileCruiseDurations && $package->nileCruiseDurations->isNotEmpty())
            <div class="mt-3 pt-3 border-top border-secondary">
                <div class="row g-2">
                    @foreach ($package->nileCruiseDurations as $dur)
                        <div class="col-md-4">
                            <div class="p-2 border border-secondary rounded repeat-box">
                                <strong class="d-block text-white">{{ $dur->title }}</strong>
                                <small class="text-light opacity-75">{{ __('Starting from:') }} <strong
                                        class="text-success">${{ number_format((float) $dur->start_from_price, 0) }}</strong></small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
