<?php

if (!function_exists('lang')) {
    function lang($record, string $field, ?string $language = null): string
    {
        if ($record === null) {
            return '';
        }

        $lang = $language ?? session('admin_lang', 'en');

        // Use the trait's trans() method — handles DB check + API fallback
        if (method_exists($record, 'trans')) {
            return $record->trans($field, $lang);
        }

        // Fallback for models without the trait
        if ($lang === 'en' || empty($lang)) {
            return (string) ($record->$field ?? '');
        }

        $langData = $record->$lang ?? null;
        if (is_array($langData) && isset($langData[$field]) && $langData[$field] !== '') {
            return (string) $langData[$field];
        }

        return (string) ($record->$field ?? '');
    }
}

if (!function_exists('currentLang')) {
    function currentLang(): string
    {
        return session('admin_lang', 'en');
    }
}

if (!function_exists('isRtl')) {
    function isRtl(): bool
    {
        $rtlLanguages = config('languages.rtl', ['ar', 'ur', 'he', 'fa', 'ps']);
        return in_array(session('admin_lang', 'en'), $rtlLanguages);
    }
}
