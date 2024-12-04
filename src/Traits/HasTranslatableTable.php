<?php

namespace Akuadev\LaravelTranslatableTable\Traits;

use Akuadev\LaravelTranslatableTable\Casts\TranslatedAttrCast;
use Akuadev\LaravelTranslatableTable\Casts\TranslationsAttrCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslatableTable
{
    /**
     * Localized attributes
     */
    abstract public function translatableTableAttributes(): array;

    protected static function bootHasTranslatableTable(): void
    {
        static::created(function (Model $model) {
            self::upsertTranslationsForModel($model);
        });

        static::updated(callback: function (Model $model) {
            self::upsertTranslationsForModel($model, true);
        });
    }

    public function initializeHasTranslatableTable(): void
    {
        $this->with[] = 'tableTranslations';

        if (empty($this->translatableTableAttributes())) {
            return;
        }

        $this->append(self::getI18nAttrName());
        $this->append($this->translatableTableAttributes() ?? []);

        $this->mergeCasts([self::getI18nAttrName() => TranslationsAttrCast::class]);
        foreach ($this->translatableTableAttributes() as $attribute) {
            $this->mergeCasts([$attribute => TranslatedAttrCast::class]);
        }
    }

    /**
     * This model's translations
     */
    public function tableTranslations(): HasMany
    {
        return $this->hasMany(self::getTranslatableTableRelationName());
    }

    protected function getI18nValueByKey(string $key): string
    {
        $translation = $this->tableTranslations
            ->where('locale', app()->getLocale())
            ->first();

        if ($translation == null) {
            $translation = $this->tableTranslations
                ->where('locale', app()->getFallbackLocale())
                ->first();
        }

        return $translation ? (string) $translation->{$key} : '';
    }

    public static function getTranslatableTableRelationName(): string
    {
        return get_class().config('table-translations.translation_model_suffix', 'Translation');
    }

    /**
     * Return array of locale codes ['en','uk','pl']
     */
    protected static function getLocales(): array
    {
        return array_keys(config('table-translations.locales'));
    }

    /**
     * Return attribute name for i18n languages array, ex: 'i18n'
     * $model->i18n = ['en' => ['title'=>'English Title'], 'uk' => ['title'=>'Ukrainian Title']]
     */
    public static function getI18nAttrName(): string
    {
        return (string) config('table-translations.attribute_name', 'i18n');
    }

    protected static function upsertTranslationsForModel(Model $model, $isUpdate = false): void
    {
        if (! in_array($model::getI18nAttrName(), array_keys($model->attributes))) {
            return;
        }

        if (! $model->isRelation('tableTranslations')) {
            return;
        }

        $foreignKeyName = $model->tableTranslations()->getForeignKeyName();

        $translations = $isUpdate ? $model->i18n : [];

        // TODO: support 'locale' => to key
        foreach (self::getLocales() as $locale) {

            $translations[$locale][$foreignKeyName] = $model->id;
            $translations[$locale]['locale'] = $locale;

            if ($isUpdate && ! array_key_exists($locale, $model->attributes[$model::getI18nAttrName()])) {
                continue;
            }
            foreach ($model->translatableTableAttributes() as $localizableAttribute) {
                //                dump('1', $model->attributes[$model::getI18nAttrName()][$locale]);
                if ($isUpdate && ! array_key_exists($localizableAttribute, $model->attributes[$model::getI18nAttrName()][$locale])) {
                    continue;
                }
                //                dump('2', $model->attributes[$model::getI18nAttrName()][$locale][$localizableAttribute]);
                $translations[$locale][$localizableAttribute] = $model->attributes[$model::getI18nAttrName()][$locale][$localizableAttribute] ?? null;
            }

            //            $translations[] = $tr;
            //            dump($tr);
        }

        if (empty($translations)) {
            return;
        }

        $model->tableTranslations()->upsert(
            $translations,
            [$foreignKeyName, 'locale'],
            $model->translatableTableAttributes()
        );
    }

    /**
     * Remove i18n attribute before create new record
     */
    protected function getAttributesForInsert(): array
    {
        $attrs = $this->getAttributes();
        unset($attrs[static::getI18nAttrName()]);

        return $attrs;
    }

    /**
     * Remove i18n attribute before update record
     */
    protected function getDirtyForUpdate(): array
    {
        $dirty = $this->getDirty();
        $dirty = array_diff_key($dirty, array_flip([...$this->appends, 'tableTranslations']));
        // TODO: check
        //        unset($dirty[static::getI18nAttrName()]);

        return $dirty;
    }
}
