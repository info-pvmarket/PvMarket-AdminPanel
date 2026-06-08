<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display the language management page.
     */
    public function index()
    {
        $languages = Language::active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $available    = $languages->pluck('name', 'code')->all();
        $rtl          = $languages->where('rtl', true)->pluck('code')->values()->all();
        $default      = $languages->firstWhere('is_default', true)?->code ?? 'en';

        // Dropdown: all languages in DB that are not yet active
        $isoLanguages = Language::inactiveMap();

        return view('admin.setup.languages.index', compact('available', 'rtl', 'default', 'isoLanguages'));
    }

    /**
     * Enable a language (flip is_active to true).
     * If somehow it doesn't exist yet, create it.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'regex:/^[a-z]{2,3}$/'],
            'name' => ['required', 'string', 'max:60'],
            'rtl'  => ['nullable', 'boolean'],
        ]);

        $code = strtolower(trim($request->code));

        // Check it's not already active
        $existing = Language::where('code', $code)->first();

        if ($existing && $existing->is_active) {
            return back()->with('error', "Language '{$code}' is already active.");
        }

        if ($existing) {
            // Was seeded but inactive — just enable it, update name/rtl in case edited
            $existing->update([
                'name'      => trim($request->name),
                'rtl'       => $request->boolean('rtl'),
                'is_active' => true,
            ]);
        } else {
            // Not in DB at all — create it fresh
            Language::create([
                'code'       => $code,
                'name'       => trim($request->name),
                'rtl'        => $request->boolean('rtl'),
                'is_default' => false,
                'is_active'  => true,
            ]);
        }

        return back()->with('success', "Language '{$request->name}' added successfully.");
    }

    /**
     * Update an existing language (name, RTL flag).
     */
    public function update(Request $request, string $code)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'rtl'  => ['nullable', 'boolean'],
        ]);

        $language = Language::where('code', $code)->firstOrFail();

        $language->update([
            'name' => trim($request->name),
            'rtl'  => $request->boolean('rtl'),
        ]);

        return back()->with('success', "Language '{$code}' updated.");
    }

    /**
     * Set the default language.
     */
    public function setDefault(Request $request)
    {
        $request->validate([
            'default' => ['required', 'string', 'size:2'],
        ]);

        $language = Language::where('code', $request->input('default'))->firstOrFail();
        $language->makeDefault();

        return back()->with('success', "Default language set to '{$language->code}'.");
    }

    /**
     * Deactivate a language (flip is_active to false — keeps it in DB for re-adding).
     */
    public function destroy(string $code)
    {
        $language = Language::where('code', $code)->firstOrFail();

        if ($language->is_default) {
            return back()->with('error', 'Cannot remove the default language.');
        }

        // Deactivate instead of delete — it will reappear in the dropdown
        $language->update(['is_active' => false]);

        return back()->with('success', "Language '{$code}' removed.");
    }

    /**
     * Run page translations for the given language.
     */
    public function translate(Request $request, string $code)
    {
        $request->validate([
            'pages'   => ['required', 'array', 'min:1'],
            'pages.*' => ['string'],
        ]);

        $language = Language::where('code', $code)->firstOrFail();
        $pages    = $request->input('pages', []);

        foreach ($pages as $page) {
            (new \App\Jobs\TranslatePageJob($code, $page))->handle(
                app(\App\Services\TranslationService::class)
            );
        }

        return back()->with('success',
            count($pages) . ' page(s) translated to ' . $language->name . ' successfully.'
        );
    }
}