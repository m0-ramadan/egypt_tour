<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\NileCruiseCabin;
use App\Models\NileCruiseDuration;
use App\Models\NileCruiseSeasonPrice;
use App\Models\NileCruiseSeasonPriceItem;
use App\Models\Package;
use App\Models\PaymentMethod;
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
        $package = $this->package(['adult_price' => 100, 'child_price' => 50]);

        $this->get(route('website.packages.show.simple', $package->slug))
            ->assertOk()
            ->assertSee('Book Now')
            ->assertSee('Enquiry Form')
            ->assertSee('id="reserveBookingPanel"', false)
            ->assertSee('id="reserveEnquiryPanel"', false)
            ->assertSee('id="sidebarBookingForm"', false);

        $this->get(route('website.checkout.show', [
            'slug' => $package->slug,
            'travel_date' => now()->addMonth()->toDateString(),
            'rooms' => 2,
            'adults' => 2,
            'children' => 1,
            'pricing_option' => 'category',
        ]))
            ->assertOk()
            ->assertSee('Secure Checkout')
            ->assertSee('Adult / Child Pricing')
            ->assertSee('value="2"', false)
            ->assertSee('value="category" checked', false)
            ->assertDontSee('name="nationality"', false);
    }

    public function test_checkout_recalculates_total_and_creates_travelers_before_paymob_redirect(): void
    {
        $this->configurePaymob();
        $package = $this->package(['adult_price' => 100, 'child_price' => 50]);

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
            'nile:'.$item->id,
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

    private function package(array $overrides = []): Package
    {
        return Package::create(array_merge([
            'slug' => 'checkout-'.uniqid(),
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
