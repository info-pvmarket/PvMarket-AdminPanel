<?php
namespace App\Support;

use App\Models\Market;
use Illuminate\Validation\ValidationException;

final class BannerScopes
{
    public static function identity(string $selection, string $field = 'location_id'): array
    {
        if (str_starts_with($selection, 'region:')) {
            $code = substr($selection, 7);
            $region = StorefrontScopes::regions(true)[$code] ?? null;
            if (!$region || !$region['is_active'] || !$region['countries']) {
                throw ValidationException::withMessages([$field=>'Select an active saved region.']);
            }
            return ['region_code'=>$code, 'location_id'=>null];
        }
        if ($selection === 'global') {
            $global=Market::where('is_active','!=',false)->get()->first(fn($market)=>strtolower(trim((string)$market->code))==='global');
            return ['region_code'=>null, 'location_id'=>$global?->getKey()];
        }
        $market=preg_match('/^[a-f0-9]{24}$/i',$selection) ? Market::find($selection) : null;
        if (!$market || $market->is_active === false) {
            throw ValidationException::withMessages([$field=>'Select an active country or Global.']);
        }
        return ['region_code'=>null, 'location_id'=>$market->getKey()];
    }
}
