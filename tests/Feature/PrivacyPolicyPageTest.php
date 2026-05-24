<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyPageTest extends TestCase
{
    public function test_privacy_policy_page_is_publicly_accessible(): void
    {
        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Informasi yang Kami Kumpulkan')
            ->assertSee('lpksgemilangputrabangsa.com');
    }

    public function test_homepage_footer_links_to_privacy_policy(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('privacy-policy', absolute: false), false)
            ->assertSee('Privacy Policy');
    }
}
