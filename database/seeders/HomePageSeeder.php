<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageSection;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [

            // ── 1. Hero ────────────────────────────────────────────────────
            [
                'page'      => 'home',
                'section'   => 'home_hero',
                'type'      => 'text_block',
                'title'     => 'Transforming New Energy Products & Services Trade through DaaS',
                'subtitle'  => null,
                'extra'     => ['content' => ''],
                'order'     => 1,
                'is_active' => true,
            ],

            // ── 2. Orange Overseas Group ───────────────────────────────────
            [
                'page'      => 'home',
                'section'   => 'home_about_group',
                'type'      => 'richtext',
                'title'     => 'The Orange Overseas Group: Our Foundation. Established Excellence Since 2013',
                'subtitle'  => null,
                'extra'     => [
                    'content' => '
<p>Orange Overseas FZE is the parent company powering our global solar mission. Founded in 2013 at the Hamriyah Free Zone, we focus on energy management, transition, and efficiency—covering the full New Energy products &amp; services value chain, from ESG initiatives to capacity building. The Group manages six business verticals, each strategically aligned to serve the evolving energy landscape of the Middle East and Africa.</p>
                    ',
                ],
                'order'     => 2,
                'is_active' => true,
            ],

            // ── 3. About pv.market ─────────────────────────────────────────
            [
                'page'      => 'home',
                'section'   => 'home_about_pvmarket',
                'type'      => 'richtext',
                'title'     => 'About pv.market',
                'subtitle'  => null,
                'extra'     => [
                    'content' => '
<p>pvmarket is a novel, neutral, transparent, advanced, secure, and highly regulated digital marketplace designed to streamline bulk transactions of PV components. It caters to verified members and facilitates a trustworthy environment for sellers and buyers to engage in regular trade with assurance. Available across cloud services, Android, and iOS, access is granted solely to registered users, echoing the stringent, fair practices of stock exchanges and paving the way for the digitization of solar energy trading.</p>

<p>Looking ahead, pvmarket aims to expand its portfolio to include a broader range of renewable energy products and services, such as carbon trading and cross-border energy trade, making it a central hub for the green economy.</p>
                    ',
                ],
                'order'     => 3,
                'is_active' => true,
            ],

            // ── 4. pvmarket for Solar ──────────────────────────────────────
            [
                'page'      => 'home',
                'section'   => 'home_pvmarket_solar',
                'type'      => 'richtext',
                'title'     => 'pvmarket for solar',
                'subtitle'  => null,
                'extra'     => [
                    'content' => '
<p>The governance of pvmarket demands a dynamic, cohesive, and flexible management structure dedicated to safeguarding equitable operations and catering to the EMEA region, the platform is supported by a highly competent management team both online and offline. In addition, pvmarket will serve as a reservoir of insights from thought leaders and key industry participants.</p>
                    ',
                ],
                'order'     => 4,
                'is_active' => true,
            ],

        ];

        foreach ($sections as $data) {
            PageSection::updateOrCreate(
                ['page' => $data['page'], 'section' => $data['section']],
                $data
            );
        }

        $this->command->info('✅ Home page seeded!');
    }
}
