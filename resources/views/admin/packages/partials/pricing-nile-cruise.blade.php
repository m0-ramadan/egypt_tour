<div class="pricing-type-block" id="nileCruisePricingBlock" data-pricing-type="nile_cruise">
    <div class="card mb-4 border-light bg-light p-3 w-100">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="la la-ship fs-3"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-primary">{{ __('Nile Cruise Pricing Engine') }}</h6>
                <p class="text-muted small mb-0">
                    {{ __('Nile Cruise pricing is managed dynamically based on durations, cabins, and seasonal pricing configured in the Itinerary & Nile Cruise Details step.') }}
                </p>
            </div>
        </div>

        @if(isset($package) && $package->nileCruiseDurations && $package->nileCruiseDurations->isNotEmpty())
            <div class="mt-3 pt-3 border-top">
                <div class="row g-2">
                    @foreach($package->nileCruiseDurations as $dur)
                        <div class="col-md-4">
                            <div class="p-2 border rounded bg-white">
                                <strong class="d-block text-dark">{{ $dur->title }}</strong>
                                <small class="text-muted">{{ __('Starting from:') }} <strong>${{ number_format((float)$dur->start_from_price, 0) }}</strong></small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
