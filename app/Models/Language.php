<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Language extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'languages';

    protected $fillable = [
        'code',
        'name',
        'rtl',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rtl'        => 'boolean',
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    /**
     * Returns the default language, or the first active one as fallback.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::where('is_active', true)->first();
    }

    /**
     * Sets this language as default, clearing any previous default.
     */
    public function makeDefault(): void
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    /**
     * All active languages as a code→name map for the table/dropdown.
     * ['en' => 'English', 'ar' => 'Arabic', ...]
     */
    public static function availableMap(): array
    {
        return static::active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->pluck('name', 'code')
            ->all();
    }

    /**
     * All INACTIVE languages as a code→name map for the "Add" dropdown.
     * These are the languages seeded but not yet enabled by the admin.
     * ['fr' => 'French', 'de' => 'German', ...]
     */
    public static function inactiveMap(): array
    {
        return static::inactive()
            ->orderBy('name')
            ->pluck('name', 'code')
            ->all();
    }
}