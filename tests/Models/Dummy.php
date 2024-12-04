<?php

namespace Akuadev\LaravelTranslatableTable\Tests\Models;

use Akuadev\LaravelTranslatableTable\Casts\TranslatedAttrCast;
use Akuadev\LaravelTranslatableTable\Casts\TranslationsAttrCast;
use Akuadev\LaravelTranslatableTable\Traits\HasTranslatableTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    use HasFactory;
    use HasTranslatableTable;

    protected $guarded = []; // i18n => should be fillable
    protected $appends = ['i18n', 'title', 'description'];

    protected $casts = [
//        'some_data' => TranslatedAttrCast::class,
        'title' => TranslatedAttrCast::class,
        'description' => TranslatedAttrCast::class,
        'i18n' => TranslationsAttrCast::class,
    ];

    public function i18nAttributes(): array
    {
        return ['title', 'description'];
    }
}
