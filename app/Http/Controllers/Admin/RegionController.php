<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use MongoDB\Driver\Exception\BulkWriteException;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $entries = (int) $request->query('entries', 10);
        $entries = in_array($entries, [10, 25, 50, 100], true) ? $entries : 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $regions = collect($this->regions())
            ->filter(fn (array $region) => stripos($region['code'], $search) !== false || stripos($region['name'], $search) !== false)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $records = new LengthAwarePaginator($regions->forPage($page, $entries)->values(), $regions->count(), $entries, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('admin.setup.regions.index', compact('records', 'search', 'entries'));
    }

    public function create()
    {
        return view('admin.setup.regions.form', ['region' => null, 'countries' => $this->countryOptions()]);
    }

    public function store(Request $request)
    {
        $code = $request->validate([
            'code' => ['required', 'string', 'max:48', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'],
        ])['code'];
        if (isset($this->regions()[$code])) {
            throw ValidationException::withMessages(['code' => 'This region slug is already in use.']);
        }

        $data = $this->validatedRegion($request);
        try {
            $this->collection()->insertOne(['_id' => $code] + $data);
        } catch (BulkWriteException $exception) {
            if ($exception->getCode() !== 11000) {
                throw $exception;
            }
            throw ValidationException::withMessages(['code' => 'This region slug is already in use.']);
        }

        return redirect()->route('admin.setup.regions.index')->with('success', 'Region created.');
    }

    public function edit(string $code)
    {
        $region = $this->findRegion($code);

        return view('admin.setup.regions.form', [
            'region' => $region,
            'countries' => $this->countryOptions($region['countries']),
        ]);
    }

    public function update(Request $request, string $code)
    {
        $region = $this->findRegion($code);
        $data = $this->validatedRegion($request, $region['countries']);
        try {
            $this->collection()->updateOne(
                ['_id' => $code, 'is_deleted' => ['$ne' => true]],
                ['$set' => $data],
                ['upsert' => true]
            );
        } catch (BulkWriteException $exception) {
            if ($exception->getCode() === 11000) {
                $this->findRegion($code);
            }
            throw $exception;
        }

        return redirect()->route('admin.setup.regions.index')->with('success', 'Region updated.');
    }

    public function destroy(string $code)
    {
        $region = $this->findRegion($code);
        // Keep the slug reserved and prevent storefront defaults from restoring a deleted region.
        $this->collection()->updateOne(
            ['_id' => $code],
            [
                '$set' => ['is_active' => false, 'is_deleted' => true],
                '$setOnInsert' => ['name' => $region['name'], 'countries' => $region['countries']],
            ],
            ['upsert' => true]
        );

        return redirect()->route('admin.setup.regions.index')->with('success', 'Region deleted.');
    }

    private function validatedRegion(Request $request, array $existingCountries = []): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'countries' => 'required|array|min:1|max:249',
            'countries.*' => ['required', 'string', 'distinct', Rule::in(array_keys($this->countryOptions($existingCountries)))],
            'is_active' => 'required|boolean',
        ]);
        $data['countries'] = array_values($data['countries']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function countryOptions(array $existingCountries = []): array
    {
        $countries = array_combine($existingCountries, $existingCountries);
        foreach (Country::orderBy('name')->get() as $country) {
            $code = strtoupper(trim((string) ($country->iso2 ?: $country->code)));
            if (preg_match('/\A[A-Z]{2}\z/', $code)) {
                $countries[$code] = $country->name;
            }
        }
        asort($countries, SORT_NATURAL | SORT_FLAG_CASE);

        return $countries;
    }

    private function findRegion(string $code): array
    {
        $region = $this->regions()[$code] ?? null;
        abort_if($region === null, 404);

        return $region;
    }

    private function regions(): array
    {
        $regions = [];
        foreach (config('stock_regions') as $code => $countries) {
            $regions[$code] = [
                'code' => $code,
                'name' => str($code)->replace('-', ' ')->title()->toString(),
                'countries' => explode(' ', $countries),
                'is_active' => true,
                'path' => '/' . $code,
            ];
        }

        foreach ($this->collection()->find() as $record) {
            $code = (string) $record['_id'];
            if ($record['is_deleted'] ?? false) {
                unset($regions[$code]);
                continue;
            }
            $regions[$code] = [
                'code' => $code,
                'name' => $record['name'],
                'countries' => (array) $record['countries'],
                'is_active' => (bool) $record['is_active'],
                'path' => $regions[$code]['path'] ?? '/region/' . $code,
            ];
        }

        return $regions;
    }

    private function collection()
    {
        return DB::connection('mongodb')->getCollection('stock_regions');
    }
}
