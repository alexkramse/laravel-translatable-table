<?php

namespace Akuadev\LaravelTranslatableTable\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TranslationsAttrCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return $model->tableTranslations
            ->mapWithKeys(fn ($translation) => [
                $translation->locale => $translation->only($model->translatableTableAttributes()),
            ])
            ->toArray();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return [$key => $value];
    }
}
