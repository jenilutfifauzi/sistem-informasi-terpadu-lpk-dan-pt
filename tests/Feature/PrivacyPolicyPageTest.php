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

    public function test_terms_of_service_page_is_publicly_accessible(): void
    {
        $this->get(route('terms-of-service'))
            ->assertOk()
            ->assertSee('Terms &amp; Services', false)
            ->assertSee('LPKS Gemilang Putra Bangsa')
            ->assertSee('Proses Penyaluran Tenaga Kerja');
    }

    public function test_homepage_footer_links_to_terms_of_service(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('terms-of-service', absolute: false), false)
            ->assertSee('Terms of Service');
    }

    public function test_public_pages_use_vite_assets_instead_of_tailwind_cdn(): void
    {
        foreach ([
            resource_path('views/welcome.blade.php'),
            resource_path('views/privacy-policy.blade.php'),
            resource_path('views/terms-of-service.blade.php'),
        ] as $viewPath) {
            $contents = file_get_contents($viewPath);

            $this->assertStringContainsString("@vite(['resources/css/app.css', 'resources/js/app.js'])", $contents);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $contents);
            $this->assertStringNotContainsString('type="text/tailwindcss"', $contents);
            $this->assertStringNotContainsString('tailwind.config', $contents);
        }
    }
}
