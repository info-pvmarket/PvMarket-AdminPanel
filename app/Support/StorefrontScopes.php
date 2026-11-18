<?php
namespace App\Support;

use App\Models\Market;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StorefrontScopes
{
    public static function regions(): array
    {
        $regions = [];
        foreach (config('stock_regions', []) as $code => $countries) {
            $regions[$code] = ['code'=>$code, 'name'=>str($code)->replace('-', ' ')->title()->toString(), 'countries'=>explode(' ', $countries), 'is_active'=>true, 'path'=>'/'.$code];
        }
        foreach (DB::connection('mongodb')->getCollection('stock_regions')->find() as $record) {
            $code = (string)$record['_id'];
            if ($record['is_deleted'] ?? false) { unset($regions[$code]); continue; }
            $regions[$code] = ['code'=>$code, 'name'=>$record['name'], 'countries'=>(array)$record['countries'], 'is_active'=>(bool)$record['is_active'], 'path'=>$regions[$code]['path'] ?? '/region/'.$code];
        }
        return $regions;
    }

    public static function region(?string $selection): ?array
    {
        if (!str_starts_with($selection ?? '', 'region:')) return null;
        $code = substr($selection, 7);
        $region = self::regions()[$code] ?? null;
        if (!$region || !$region['is_active'] || !$region['countries']) {
            throw ValidationException::withMessages(['market_id'=>'Select an active region.']);
        }
        return $region;
    }

    public static function options(): array
    {
        return [
            'Regions'=>collect(self::regions())->filter(fn($r)=>$r['is_active'] && $r['countries'])->sortBy('name')->mapWithKeys(fn($r)=>['region:'.$r['code']=>$r['name']])->all(),
            'Countries'=>Market::where('is_active', true)->orderBy('name')->get()->reject(fn($m)=>strtolower(trim($m->code)) === 'global')->mapWithKeys(fn($m)=>[(string)$m->id=>$m->name.' ('.strtoupper($m->code).')'])->all(),
        ];
    }
}
