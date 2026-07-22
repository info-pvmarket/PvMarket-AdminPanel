<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageSection;

class DisclaimerPageSeeder extends Seeder
{
    public function run(): void
    {
        // Clear old records first
        PageSection::where('page', 'disclaimer')->delete();

        $sections = [

            // ── PAGE HEADER ────────────────────────────────
            [
                'page'      => 'disclaimer',
                'section'   => 'disclaimer_header',
                'type'      => 'text_block',
                'title'     => 'Disclaimer',
                'subtitle'  => null,
                'extra'     => [
                    'content' => 'Important legal information about the use of this website and services as per the terms and conditions of https://pv.market/',
                ],
                'order'     => 1,
                'is_active' => true,
            ],

            // ── DISCLAIMER SECTIONS AS CARDS ───────────────
            // Uses 'faq' type: each item has a 'question' (section title)
            // and 'answer' (rich content) — admin can edit each card separately
            // and use "Add Question" button to add more sections
            [
    'page'      => 'disclaimer',
    'section'   => 'disclaimer_sections',
    'type'      => 'sections',   // ← changed from 'faq'
    'title'     => 'Disclaimer Sections',
    'subtitle'  => null,
    'extra'     => [
        'items' => [
            [
                'title'   => 'General Disclaimer',
                'content' => '<p>The information provided on this website is for general informational purposes only. All information on the site is provided in good faith, however we make no representation or warranty of any kind, express or implied, regarding the accuracy, adequacy, validity, reliability, availability, or completeness of any information on the site.</p><p>Under no circumstance shall we have any liability to you for any loss or damage of any kind incurred as a result of the use of the site or reliance on any information provided on the site. Your use of the site and your reliance on any information on the site is solely at your own risk.</p>',
            ],
            [
                'title'   => 'External Links Disclaimer',
                'content' => '<p>This website may contain links to external websites that are not provided or maintained by or in any way affiliated with us. Please note that we do not guarantee the accuracy, relevance, timeliness, or completeness of any information on these external websites.</p>',
            ],
            [
                'title'   => 'Professional Disclaimer',
                'content' => '<p>The site cannot and does not contain professional advice. The information is provided for general informational and educational purposes only and is not a substitute for professional advice. Accordingly, before taking any actions based upon such information, we encourage you to consult with the appropriate professionals.</p><p>The use or reliance of any information contained on this site is solely at your own risk.</p>',
            ],
            [
                'title'   => 'Testimonials Disclaimer',
                'content' => '<p>This website may contain testimonials by users of our products and/or services. These testimonials reflect the real-life experiences and opinions of such users. However, the experiences are personal to those particular users, and may not necessarily be representative of all users of our products and/or services.</p><p>We do not claim, and you should not assume, that all users will have the same experiences. Your individual results may vary.</p>',
            ],
            [
                'title'   => 'CONTACT US',
                'content' => '<p>For more information about our privacy practices, if you have questions, or if you would like to make a complaint, please contact us by e-mail or mail using the details provided below:</p><p>Should you have other questions or concerns about these privacy policies, or wish to exercise any of the above data subject rights, please contact us using our Contact Us section, <a href="https://pv.market/contact-us/" target="_blank">https://pv.market/contact-us/</a></p><p>Email: <a href="mailto:info@pv.market">info@pv.market</a> — Write a mail with Order number.</p><p>Phone: <a href="tel:+971523825549">+971 523825549</a> — Call us with Order number.</p>',
            ],
        ],
    ],
    'order'     => 2,
    'is_active' => true,
],

        ];

        foreach ($sections as $data) {
            PageSection::updateOrCreate(
                ['page' => $data['page'], 'section' => $data['section']],
                $data
            );
        }

        $this->command->info('✅ Disclaimer page seeded!');
    }
}
