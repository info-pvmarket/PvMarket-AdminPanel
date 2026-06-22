<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // RoleSeeder::class,
            ContactPageSeeder::class,
            AboutPageSeeder::class,
            FaqPageSeeder::class,
            PrivacyPageSeeder::class,
            TermsPageSeeder::class,
            DeliveryReturnPageSeeder::class,
            DisclaimerPageSeeder::class,
            HomePageSeeder::class,
            CustomerSupportPageSeeder::class,
        ]);
    }

    
}