<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\NileCruiseCabin;
use App\Models\NileCruiseDuration;
use App\Models\NileCruiseSeasonPrice;
use App\Models\NileCruiseSeasonPriceItem;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\TourPackageAccommodation;
use App\Models\TourPackagePriceItem;
use App\Models\TourPackageSeason;
use App\Services\PackageBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WebsiteCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_without_real_price_is_enquiry_only(): void
    {
        $package = $this->package();

        $this->get(route('website.packages.show.simple', $package->slug))
            ->assertOk()
            ->assertSee('Submit Enquiry')
            ->assertDontSee('Book Now')
            ->assertDontSee('name="nationality"', false);

        $this->get(route('website.checkout.show', $package->slug))->assertNotFound();
    }

    public function test_priced_package_shows_booking_and_enquiry_actions(): void
    {
        $package = $this->package(['package_type' => 'day_tour', 'adult_price' => 100, 'child_price' => 50]);

        $this->get(route('website.packages.show.simple', $package->slug))
            ->assertOk()
            ->assertSee('Book Now')
            ->assertSee('Enquiry Form')
            ->assertSee('id="reserveBookingPanel"', false)
            ->assertSee('id="reserveEnquiryPanel"', false)
            ->assertSee('id="sidebarBookingForm"', false)
            ->assertDontSee('Estimated total');

        $response = $this->get(route('website.checkout.show', [
            'slug' => $package->slug,
            'travel_date' => now()->addMonth()->toDateString(),
            'rooms' => 2,
            'adults' => 2,
            'children' => 1,
            'pricing_option' => 'category',
        ]));

        $response
            ->assertOk()
            ->assertSee('Secure Checkout')
            ->assertSee('Adult / Child Pricing')
            ->assertSee('value="2"', false)
            ->assertDontSee('name="nationality"', false);

        $this->assertMatchesRegularExpression('/value="category"\s+checked/', $response->getContent());
    }

    public function test_day_tour_booking_calendar_receives_the_configured_operating_days(): void
    {
        $package = $this->package([
            'package_type' => 'day_tour',
            'adult_price' => 100,
            'child_price' => 50,
            'operating_days' => ['wednesday'],
        ]);

        $this->get(route('website.packages.show.simple', $package->slug))
            ->assertOk()
            ->assertSee('id="dayTourCalendar"', false)
            ->assertSee('id="dayTourPriceBox"', false)
            ->assertSee('sidebar-price-options d-none', false)
            ->assertSee('id="sidebar_adults"', false)
            ->assertSee('id="sidebar_children"', false)
            ->assertSee('id="sidebar_infants"', false)
            ->assertSee('Adults (12+ years)')
            ->assertSee('Children (2–11 years)')
            ->assertSee('Infants (Under 2 years)')
            ->assertSee('data-operating-days=', false)
            ->assertSee('wednesday');

        $checkoutResponse = $this->get(route('website.checkout.show', [
            'slug' => $package->slug,
            'travel_date' => now()->next('Wednesday')->toDateString(),
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
            'pricing_option' => 'category',
        ]));

        $checkoutResponse
            ->assertOk()
            ->assertDontSee('Number of Rooms')
            ->assertDontSee('Number of Cabins')
            ->assertSee('name="rooms" type="hidden"', false)
            ->assertSee('name="infants"', false);
    }

    public function test_day_tour_quote_rejects_a_date_outside_its_operating_days(): void
    {
        $package = $this->package([
            'package_type' => 'day_tour',
            'adult_price' => 100,
            'operating_days' => ['wednesday'],
        ]);
        $package = app(PackageBookingService::class)->loadForCheckout($package->slug);
        $unavailableDate = now()->next('Thursday')->toDateString();

        try {
            app(PackageBookingService::class)->quote(
                $package,
                'category',
                $unavailableDate,
                2,
                0,
                0,
                1,
            );
            $this->fail('An unavailable weekday was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('travel_date', $exception->errors());
        }
    }

    public function test_daily_day_tour_accepts_any_future_weekday(): void
    {
        $package = $this->package([
            'package_type' => 'day_tour',
            'adult_price' => 100,
            'operating_days' => ['daily'],
        ]);
        $package = app(PackageBookingService::class)->loadForCheckout($package->slug);

        $quote = app(PackageBookingService::class)->quote(
            $package,
            'category',
            now()->next('Thursday')->toDateString(),
            2,
            0,
            0,
            1,
        );

        $this->assertSame(200.0, $quote['total']);
    }

    public function test_checkout_recalculates_total_and_creates_travelers_before_paymob_redirect(): void
    {
        $this->configurePaymob();
        $package = $this->package(['adult_price' => 100, 'child_price' => 50]);
        $package = $this->package(['package_type' => 'day_tour', 'adult_price' => 100, 'child_price' => 50]);

        Http::fake([
            'https://accept.paymob.com/v1/intention/' => Http::response([
                'id' => 'intention-1',
                'intention_order_id' => 'order-1',
                'client_secret' => 'safe-test-client-secret',
            ], 201),
        ]);

        $response = $this->post(route('website.checkout.store', $package->slug), [
            'pricing_option' => 'category',
            'travel_date' => now()->addMonth()->toDateString(),
            'rooms' => 1,
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'email' => 'lead@example.test',
            'phone' => '+201000000000',
            'travelers' => [
                ['title' => 'Mr', 'first_name' => 'Lead', 'last_name' => 'Guest'],
                ['title' => 'Mrs', 'first_name' => 'Second', 'last_name' => 'Guest'],
                ['title' => 'Miss', 'first_name' => 'Child', 'last_name' => 'Guest'],
            ],
            'payment_method' => 'paymob',
            'terms' => 1,
            // Deliberately forged: the controller must ignore browser totals.
            'calculated_total' => 1,
        ]);

        $response->assertRedirectContains('accept.paymob.com/unifiedcheckout');
        $booking = Booking::query()->sole();
        $this->assertSame('250.00', (string) $booking->total_amount);
        $this->assertSame(3, $booking->travelers()->count());
        $this->assertSame('250.00', (string) $booking->items()->sole()->total_amount);
        $this->assertDatabaseHas('payments', ['booking_id' => $booking->id, 'amount' => 250]);
    }

    public function test_nile_cruise_rejects_more_cabins_than_inventory(): void
    {
        $package = $this->package(['package_type' => 'nile_cruise']);
        $cabin = NileCruiseCabin::create([
            'package_id' => $package->id,
            'name' => 'Deluxe Cabin',
            'quantity' => 1,
            'max_adults' => 2,
            'max_children' => 1,
        ]);
        $duration = NileCruiseDuration::create([
            'package_id' => $package->id,
            'title' => '4 Nights',
            'days' => 5,
            'nights' => 4,
            'is_active' => true,
        ]);
        $season = NileCruiseSeasonPrice::create([
            'package_id' => $package->id,
            'nile_cruise_duration_id' => $duration->id,
            'season_name' => ['en' => 'High Season'],
            'date_from' => now()->toDateString(),
            'date_to' => now()->addMonths(2)->toDateString(),
            'is_active' => true,
        ]);
        $item = NileCruiseSeasonPriceItem::create([
            'nile_cruise_season_price_id' => $season->id,
            'nile_cruise_cabin_id' => $cabin->id,
            'occupancy_type' => 'double',
            'price' => 200,
        ]);
        $package = app(PackageBookingService::class)->loadForCheckout($package->slug);

        $this->expectException(ValidationException::class);
        app(PackageBookingService::class)->quote(
            $package,
            'nile:' . $item->id,
            now()->addMonth(),
            2,
            0,
            0,
            2,
        );
    }

    public function test_paypal_order_is_created_and_captured_before_booking_is_marked_paid(): void
    {
        Config::set('services.paypal.enabled', true);
        Config::set('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');
        Config::set('services.paypal.client_id', 'client-id');
        Config::set('services.paypal.secret', 'client-secret');
        PaymentMethod::query()->updateOrCreate(['code' => 'paypal'], [
            'name' => 'PayPal',
            'provider' => 'paypal',
            'currency_code' => 'USD',
            'is_active' => true,
        ]);
        $package = $this->package(['adult_price' => 125]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'token-1']),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'order-123',
                'status' => 'PAYER_ACTION_REQUIRED',
                'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/approve/order-123']],
            ], 201),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/order-123/capture' => Http::response([
                'id' => 'order-123',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'payments' => ['captures' => [[
                        'id' => 'capture-123',
                        'status' => 'COMPLETED',
                        'amount' => ['currency_code' => 'USD', 'value' => '125.00'],
                        'create_time' => now()->toIso8601String(),
                    ]]],
                ]],
            ]),
        ]);

        $this->post(route('website.checkout.store', $package->slug), [
            'pricing_option' => 'category',
            'travel_date' => now()->addMonth()->toDateString(),
            'rooms' => 1,
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
            'email' => 'paypal@example.test',
            'phone' => '+201000000001',
            'travelers' => [['title' => 'Ms', 'first_name' => 'Pay', 'last_name' => 'Pal']],
            'payment_method' => 'paypal',
            'terms' => 1,
        ])->assertRedirect('https://paypal.test/approve/order-123');

        $payment = \App\Models\Payment::query()->sole();
        $this->assertSame('pending', $payment->status);

        $this->get(route('website.checkout.paypal.capture', [
            'reference' => $payment->transaction_reference,
            'token' => 'order-123',
        ]))->assertRedirect();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $payment->booking->fresh()->payment_status);
    }

    public function test_travel_package_room_based_booking_and_checkout(): void
    {
        $this->configurePaymob();

        $package = $this->package([
            'title' => ['en' => 'Cairo & Luxor Tour Package'],
            'package_type' => 'travel_package',
        ]);

        $accommodation = TourPackageAccommodation::create([
            'package_id' => $package->id,
            'name' => 'Standard',
            'is_active' => true,
        ]);

        $season = TourPackageSeason::create([
            'package_id' => $package->id,
            'accommodation_id' => $accommodation->id,
            'name' => ['en' => 'Winter (October to April)'],
            'date_from' => '2026-10-01',
            'date_to' => '2027-04-30',
            'is_active' => true,
        ]);

        TourPackagePriceItem::create([
            'season_id' => $season->id,
            'occupancy_type' => 'single',
            'price' => 1500,
            'is_active' => true,
        ]);
        TourPackagePriceItem::create([
            'season_id' => $season->id,
            'occupancy_type' => 'double',
            'price' => 1000,
            'is_active' => true,
        ]);
        TourPackagePriceItem::create([
            'season_id' => $season->id,
            'occupancy_type' => 'triple',
            'price' => 800,
            'is_active' => true,
        ]);

        // 1. Details page shows the travel package room booking form
        $this->get(route('website.packages.show.simple', $package->slug))
            ->assertOk()
            ->assertSee('id="sidebarTravelPackageForm"', false)
            ->assertSee('id="tp_rooms"', false)
            ->assertSee('Standard');

        // 2. Checkout GET page with 2 rooms: Room 1 (2 adults, 1 child) = $2,500; Room 2 (1 adult) = $1,500
        $checkoutGet = $this->get(route('website.checkout.show', [
            'slug' => $package->slug,
            'travel_date' => '2026-11-15',
            'accommodation' => 'Standard',
            'rooms' => 2,
            'room_1_adults' => 2,
            'room_1_children' => 1,
            'room_2_adults' => 1,
            'room_2_children' => 0,
        ]));

        $checkoutGet
            ->assertOk()
            ->assertSee('Standard')
            ->assertSee('Room 1')
            ->assertSee('Room 2')
            ->assertSee(__('Room') . ' 1')
            ->assertSee(__('Room') . ' 2')
            ->assertSee('2,500')
            ->assertSee('1,500')
            ->assertSee('4,000')
            ->assertSee('2,000'); // 50% deposit

        // 3. Checkout POST submission
        Http::fake([
            'https://accept.paymob.com/v1/intention/' => Http::response([
                'id' => 'intention-tp-1',
                'intention_order_id' => 'order-tp-1',
                'client_secret' => 'safe-tp-client-secret',
            ], 201),
        ]);

        $response = $this->post(route('website.checkout.store', $package->slug), [
            'pricing_option' => 'travel_package',
            'accommodation' => 'Standard',
            'travel_date' => '2026-11-15',
            'rooms' => 2,
            'adults' => 3,
            'children' => 1,
            'infants' => 0,
            'room_1_adults' => 2,
            'room_1_children' => 1,
            'room_2_adults' => 1,
            'room_2_children' => 0,
            'lead_title' => 'Mr',
            'lead_first_name' => 'John',
            'lead_last_name' => 'Doe',
            'traveler_2_title' => 'Mrs',
            'traveler_2_first_name' => 'Jane',
            'traveler_2_last_name' => 'Doe',
            'traveler_3_title' => 'Miss',
            'traveler_3_first_name' => 'Alice',
            'traveler_3_last_name' => 'Doe',
            'traveler_4_title' => 'Mr',
            'traveler_4_first_name' => 'Robert',
            'traveler_4_last_name' => 'Smith',
            'email' => 'johndoe@example.test',
            'phone' => '+201012345678',
            'country' => 'United States',
            'pickup_location' => 'Cairo International Airport Terminal 3',
            'special_requests' => 'Quiet rooms on high floor please.',
            'payment_method' => 'paymob',
            'terms' => 1,
        ]);

        $response->assertRedirectContains('accept.paymob.com/unifiedcheckout');

        $booking = Booking::query()->where('package_id', $package->id)->sole();
        $this->assertSame('4000.00', (string) $booking->total_amount);
        $this->assertSame(4, $booking->travelers()->count());
        $this->assertSame('Cairo International Airport Terminal 3', $booking->pickup_location);
        $this->assertSame('4000.00', (string) $booking->items()->sole()->total_amount);
        $this->assertSame(2000.0, (float) $booking->checkout_details['deposit_amount']);
        $this->assertSame(2000.0, (float) $booking->checkout_details['remaining_balance']);
        $this->assertCount(2, $booking->checkout_details['room_breakdown']);
    }

    public function test_travel_package_without_seeded_accommodations_uses_fallback_tiers_and_books_successfully(): void
    {
        $package = $this->package([
            'title' => ['en' => 'Coptic Cairo 2 Days'],
            'package_type' => 'travel_package',
            'adult_price' => 500,
        ]);

        // Details page shows room booking form with fallback tiers
        $this->get(route('website.packages.show.simple', $package->slug))
            ->assertOk()
            ->assertSee('id="sidebarTravelPackageForm"', false)
            ->assertSee('id="tp_rooms"', false)
            ->assertSee('Standard')
            ->assertSee('Deluxe')
            ->assertSee('Luxury');

        // Checkout page works with fallback tier
        $checkoutGet = $this->get(route('website.checkout.show', [
            'slug' => $package->slug,
            'pricing_option' => 'travel_package',
            'travel_date' => '2026-11-15',
            'rooms' => 1,
            'room_1_accommodation' => 'Standard',
            'room_1_adults' => 2,
            'room_1_children' => 0,
        ]));

        $checkoutGet
            ->assertOk()
            ->assertSee('Standard')
            ->assertSee(__('Room') . ' 1');
    }

    public function test_day_tour_booking_with_infants_saves_and_displays_in_dashboard(): void
    {
        $this->configurePaymob();

        $package = $this->package([
            'title' => ['en' => 'Pyramids Day Tour'],
            'package_type' => 'day_tour',
            'adult_price' => 100,
            'child_price' => 50,
            'infant_price' => 0,
        ]);

        Http::fake([
            'https://accept.paymob.com/v1/intention/' => Http::response([
                'id' => 'intention-dt-infant-1',
                'intention_order_id' => 'order-dt-infant-1',
                'client_secret' => 'safe-dt-client-secret',
            ], 201),
        ]);

        $travelDate = now()->addMonth()->toDateString();
        $response = $this->post(route('website.checkout.store', $package->slug), [
            'pricing_option' => 'category',
            'travel_date' => $travelDate,
            'rooms' => 1,
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
            'lead_title' => 'Mr',
            'lead_first_name' => 'John',
            'lead_last_name' => 'Doe',
            'traveler_2_title' => 'Mrs',
            'traveler_2_first_name' => 'Jane',
            'traveler_2_last_name' => 'Doe',
            'traveler_3_title' => 'Mr',
            'traveler_3_first_name' => 'Leo',
            'traveler_3_last_name' => 'Doe',
            'traveler_4_title' => 'Miss',
            'traveler_4_first_name' => 'Mia',
            'traveler_4_last_name' => 'Doe',
            'email' => 'john.infant@example.test',
            'phone' => '+201099887766',
            'country' => 'United States',
            'pickup_location' => 'Four Seasons Hotel Cairo',
            'special_requests' => 'Baby car seat needed.',
            'payment_method' => 'paymob',
            'terms' => 1,
        ]);

        $response->assertRedirectContains('accept.paymob.com/unifiedcheckout');

        $booking = Booking::query()->where('package_id', $package->id)->sole();
        $this->assertSame(2, $booking->adults);
        $this->assertSame(1, $booking->children);
        $this->assertSame(1, $booking->infants);
        $this->assertSame(4, $booking->travellers_count);
        $this->assertSame(4, $booking->travelers()->count());

        $infantTraveler = $booking->travelers()->where('traveler_type', 'infant')->first();
        $this->assertNotNull($infantTraveler);
        $this->assertSame('Mia', $infantTraveler->first_name);

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin_infant_test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee('4')
            ->assertSee('2 بالغين · 1 أطفال · 1 رضع')
            ->assertSee('رضيع (Infant)')
            ->assertSee('Mia Doe');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee('2 بالغ · 1 طفل · 1 رضيع');
    }

    public function test_checkout_displays_full_world_countries_list(): void
    {
        $package = $this->package(['package_type' => 'day_tour', 'adult_price' => 100, 'child_price' => 50]);

        \App\Models\Country::create(['name' => ['en' => 'Egypt'], 'code' => 'EG', 'slug' => 'egypt', 'is_active' => true]);

        $response = $this->get(route('website.checkout.show', [
            'slug' => $package->slug,
            'travel_date' => now()->addMonth()->toDateString(),
            'rooms' => 1,
            'adults' => 1,
            'pricing_option' => 'category',
        ]));

        $response->assertOk()
            ->assertSee('value="Egypt"', false)
            ->assertSee('value="United States"', false)
            ->assertSee('value="United Kingdom"', false)
            ->assertSee('value="Germany"', false)
            ->assertSee('value="France"', false)
            ->assertSee('value="Saudi Arabia"', false)
            ->assertSee('value="United Arab Emirates"', false)
            ->assertSee('value="Japan"', false)
            ->assertSee('value="Australia"', false)
            ->assertSee('value="Canada"', false);

        $arResponse = $this->withSession(['locale' => 'ar'])->get(route('website.checkout.show', [
            'slug' => $package->slug,
            'travel_date' => now()->addMonth()->toDateString(),
            'rooms' => 1,
            'adults' => 1,
            'pricing_option' => 'category',
        ]));

        $arResponse->assertOk()
            ->assertSee('مصر')
            ->assertSee('الولايات المتحدة')
            ->assertSee('المملكة العربية السعودية')
            ->assertSee('ألمانيا')
            ->assertSee('فرنسا');
    }

    private function package(array $overrides = []): Package
    {
        return Package::create(array_merge([
            'slug' => 'checkout-' . uniqid(),
            'title' => ['en' => 'Egypt Adventure'],
            'package_type' => 'travel_package',
            'is_active' => true,
        ], $overrides));
    }

    private function configurePaymob(): void
    {
        Config::set('services.paymob.enabled', true);
        Config::set('services.paymob.base_url', 'https://accept.paymob.com');
        Config::set('services.paymob.checkout_url', 'https://accept.paymob.com/unifiedcheckout/');
        Config::set('services.paymob.secret_key', 'secret');
        Config::set('services.paymob.public_key', 'public');
        Config::set('services.paymob.hmac_secret', 'hmac');
        Config::set('services.paymob.integration_ids', [123]);
        PaymentMethod::query()->updateOrCreate(['code' => 'paymob'], [
            'name' => 'Paymob',
            'provider' => 'paymob',
            'currency_code' => 'USD',
            'is_active' => true,
        ]);
    }
}
