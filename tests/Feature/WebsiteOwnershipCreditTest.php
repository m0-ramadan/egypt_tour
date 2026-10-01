<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebsiteOwnershipCreditTest extends TestCase
{
    public function test_public_footer_contains_the_canonical_ezycode_credit(): void
    {
        $this->get(route('website.home'))
            ->assertOk()
            ->assertSee('Copyright to')
            ->assertSee('EZYCODE')
            ->assertSee('href="https://ezycode.dev/"', false);
    }
}
