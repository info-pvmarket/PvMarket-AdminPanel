<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Market;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        $currencies = Currency::orderBy('code')->get();

        return view('admin.setup.currencies.index', compact('currencies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:3', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'symbol' => ['required', 'string', 'max:16'],
        ]);

        $code = strtoupper(trim($validated['code']));

        if (Currency::where('code', $code)->exists()) {
            return $this->redirectToIndex()
                ->withErrors(['code' => "{$code} is already available."])
                ->withInput();
        }

        Currency::create([
            'code' => $code,
            'symbol' => trim($validated['symbol']),
        ]);

        return $this->redirectToIndex()->with('success', "{$code} added successfully.");
    }

    public function updateSymbol(Request $request, string $code): RedirectResponse
    {
        $validated = $request->validate([
            'symbol' => ['required', 'string', 'max:16'],
        ]);

        $code = strtoupper(trim($code));
        $currency = Currency::where('code', $code)->firstOrFail();
        $currency->update(['symbol' => trim($validated['symbol'])]);

        return $this->redirectToIndex()->with('success', "{$code} symbol updated successfully.");
    }

    public function destroy(string $code): RedirectResponse
    {
        $code = strtoupper(trim($code));
        $defaultMarkets = Market::where('default_currency', $code)->pluck('name')->all();

        if ($defaultMarkets !== []) {
            return $this->redirectToIndex()->withErrors([
                'currency' => "{$code} cannot be removed because it is the default currency for: "
                    . implode(', ', $defaultMarkets)
                    . '. Change those market defaults first.',
            ]);
        }

        Currency::where('code', $code)->firstOrFail()->delete();

        return $this->redirectToIndex()->with('success', "{$code} removed successfully.");
    }

    private function redirectToIndex(): RedirectResponse
    {
        return redirect()->route('admin.setup.currencies.index');
    }
}
