<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Move the legacy market-specific currency codes into one common collection.
     */
    public function up(): void
    {
        $connection = DB::connection('mongodb');
        $currencies = [];
        $knownSymbols = [
            'AED' => 'د.إ',
            'CNY' => '¥',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            'PKR' => '₨',
            'USD' => '$',
            'ZAR' => 'R',
        ];

        foreach ($connection->table('market_settings')->get() as $settings) {
            $symbols = (array) data_get($settings, 'currency_symbols', []);

            foreach ((array) data_get($settings, 'available_currencies', []) as $code) {
                $code = strtoupper(trim((string) $code));
                if (preg_match('/^[A-Z0-9]{3,10}$/', $code) !== 1) {
                    continue;
                }

                $currencies[$code] = trim((string) ($symbols[$code] ?? $knownSymbols[$code] ?? $code));
            }
        }

        foreach ($connection->table('markets')->get() as $market) {
            $code = strtoupper(trim((string) data_get($market, 'default_currency', '')));
            if ($code !== '' && preg_match('/^[A-Z0-9]{3,10}$/', $code) === 1) {
                $currencies[$code] ??= $knownSymbols[$code] ?? $code;
            }
        }

        if ($currencies === []) {
            $currencies['USD'] = '$';
        }

        ksort($currencies);

        foreach ($currencies as $code => $symbol) {
            $exists = $connection->table('currencies')->where('code', $code)->exists();
            if ($exists) {
                continue;
            }

            $connection->table('currencies')->insert([
                'code' => $code,
                'symbol' => $symbol,
                'migration_source' => 'global-currency-v1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::connection('mongodb')
            ->table('currencies')
            ->where('migration_source', 'global-currency-v1')
            ->delete();
    }
};
