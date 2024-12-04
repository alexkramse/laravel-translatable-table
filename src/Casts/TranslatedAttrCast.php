<?php

namespace Alexkramse\LaravelTranslatableTable\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TranslatedAttrCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        $currentLocale = app()->getLocale();
        $fallbackLocale = app()->getFallbackLocale();

        $translation = $model->tableTranslations
            ->when(
                $currentLocale === $fallbackLocale,
                fn ($query) => $query->where('locale', $currentLocale),
                fn ($query) => $query->whereIn('locale', [$currentLocale, $fallbackLocale])
            )
            ->keyBy('locale')
            ->toArray();

        return $translation[$currentLocale][$key] ?? $translation[$fallbackLocale][$key] ?? null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return trim($value);
    }
}
