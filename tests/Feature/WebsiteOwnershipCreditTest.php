<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteOwnershipCreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_footer_contains_copyright_without_ezycode_credit(): void
    {
        $this->get(route('website.home'))
            ->assertOk()
            ->assertSee('Copyright')
            ->assertSee('Egypt Tour Pro')
            ->assertDontSee('EZYCODE')
            ->assertDontSee('https://ezycode.dev/');
    }
}
