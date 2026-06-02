<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $entries = (int) $request->get('entries', 10);
        $search  = $request->get('search');

        $query = Location::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('country_name', 'like', "%{$search}%")
                  ->orWhere('domain_name',  'like', "%{$search}%");
            });
        }

        $records = $query->latest()->paginate($entries)->withQueryString();

        return view('admin.setup.locations.locations', [
            'mode'    => 'index',
            'records' => $records,
        ]);
    }

    // ─── CREATE ──────────────────────────────────────────────────────────────

    public function create()
    {
        $countries = Country::orderBy('name')->get();

        return view('admin.setup.locations.locations', [
            'mode'      => 'create',
            'countries' => $countries,
        ]);
    }

    // ─── STORE ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $rows = $request->input('locations', []);

        if (empty($rows)) {
            return back()->withErrors('Please add at least one location row.');
        }

        $errors = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 1;

            if (empty($row['country_id'])) {
                $errors[] = "Row {$rowNum}: Country is required.";
                continue;
            }

            if (empty($row['domain_name'])) {
                $errors[] = "Row {$rowNum}: Domain name is required.";
                continue;
            }

            // Resolve country name
            if ($row['country_id'] === 'global') {
                $countryName = 'Global';
            } else {
                $country = Country::find($row['country_id']);
                $countryName = $country ? $country->name : $row['country_id'];
            }

            Location::create([
    'country_id'   => $row['country_id'] === 'global'
                        ? null
                        : $row['country_id'],
    'country_name' => $countryName,
    'domain_name'  => trim($row['domain_name']),
]);
        }

        if (!empty($errors)) {
            return back()->withErrors(implode(' | ', $errors));
        }

        return redirect()->route('admin.setup.locations.index')
                         ->with('success', 'Location(s) saved successfully.');
    }

    // ─── EDIT ────────────────────────────────────────────────────────────────

    public function edit(string $id)
    {
        $record    = Location::findOrFail($id);
        $countries = Country::orderBy('name')->get();

        return view('admin.setup.locations.locations', [
            'mode'      => 'edit',
            'record'    => $record,
            'countries' => $countries,
        ]);
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $request->validate([
            'country_id'  => 'required|string',
            'domain_name' => 'required|string|max:255',
        ]);

        $record = Location::findOrFail($id);

        if ($request->country_id === 'global') {
            $countryName = 'Global';
        } else {
            $country     = Country::find($request->country_id);
            $countryName = $country ? $country->name : $request->country_id;
        }

        $record->update([
    'country_id'   => $request->country_id === 'global'
                        ? null
                        : $request->country_id,
    'country_name' => $countryName,
    'domain_name'  => trim($request->domain_name),
]);

        return redirect()->route('admin.setup.locations.index')
                         ->with('success', 'Location updated successfully.');
    }

    // ─── DESTROY ─────────────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        Location::findOrFail($id)->delete();

        return redirect()->route('admin.setup.locations.index')
                         ->with('success', 'Location deleted successfully.');
    }
}