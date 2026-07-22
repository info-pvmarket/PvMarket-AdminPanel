<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSupportPageSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'page'        => 'customer_support',
                'section'     => 'hero',
                'type'        => 'customer_support',
                'title'       => "We're here to help",
                'subtitle'    => "Reach out to our support team and we'll get back to you shortly.",
                'description' => null,
                'button_text' => null,
                'button_link' => null,
                'extra'       => json_encode([
                    'email' => 'info@pv.market',
                    'phone' => '+971 523825549',
                ]),
                'is_active'   => true,
                'sort_order'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($sections as $section) {
            DB::table('page_sections')->updateOrInsert(
                [
                    'page'    => $section['page'],
                    'section' => $section['section'],
                ],
                $section
            );
        }

        // Ensure the page setting row exists for SEO
        DB::table('page_settings')->updateOrInsert(
            ['page' => 'customer_support'],
            [
                'seo_title'       => 'Customer Support – pv.market',
                'seo_description' => 'Get in touch with the pv.market support team. We\'re here to help you with any questions or issues.',
                'seo_keywords'    => 'customer support, help, contact, pv.market',
                'is_published'    => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );
    }
}
