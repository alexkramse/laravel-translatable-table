<?php

namespace Alexkramse\LaravelTranslatableTable\Traits;

use Alexkramse\LaravelTranslatableTable\Casts\TranslatedAttrCast;
use Alexkramse\LaravelTranslatableTable\Casts\TranslationsAttrCast;
use Illuminate\Database\Eloquent\Builder;
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

        $this->append(self::getTranslationsAttrName());
        $this->append($this->translatableTableAttributes() ?? []);

        $this->mergeCasts([self::getTranslationsAttrName() => TranslationsAttrCast::class]);
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

    protected function getTranslationsValueByKey(string $key): string
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
     * Return attribute name for Translations languages array, ex: 'Translations'
     * $model->translations = ['en' => ['title'=>'English Title'], 'uk' => ['title'=>'Ukrainian Title']]
     */
    public static function getTranslationsAttrName(): string
    {
        return (string) config('table-translations.attribute_name', 'Translations');
    }

    protected static function upsertTranslationsForModel(Model $model, $isUpdate = false): void
    {
        if (! in_array($model::getTranslationsAttrName(), array_keys($model->attributes))) {
            return;
        }

        if (! $model->isRelation('tableTranslations')) {
            return;
        }

        $foreignKeyName = $model->tableTranslations()->getForeignKeyName();

        $translations = $isUpdate ? $model->translations : [];

        // TODO: support 'locale' => to key
        foreach (self::getLocales() as $locale) {

            if ($isUpdate && ! isset($translations[$locale])) {
                continue;
            }

            $translations[$locale][$foreignKeyName] = $model->id;
            $translations[$locale]['locale'] = $locale;

            foreach ($model->translatableTableAttributes() as $localizableAttribute) {
                //                dump('1', $model->attributes[$model::getTranslationsAttrName()][$locale]);
                if ($isUpdate && ! array_key_exists($localizableAttribute, $model->attributes[$model::getTranslationsAttrName()][$locale])) {
                    continue;
                }
                //                dump('2', $model->attributes[$model::getTranslationsAttrName()][$locale][$localizableAttribute]);
                $translations[$locale][$localizableAttribute] = $model->attributes[$model::getTranslationsAttrName()][$locale][$localizableAttribute] ?? null;
            }
        }

        if (empty($translations)) {
            return;
        }

        $model->tableTranslations()->upsert(
            $translations,
            [$foreignKeyName, 'locale'],
            $model->translatableTableAttributes()
        );

        $model->load('tableTranslations');
    }

    /**
     * Remove Translations attribute before create new record
     */
    protected function getAttributesForInsert(): array
    {
        $attrs = $this->getAttributes();
        unset($attrs[static::getTranslationsAttrName()]);

        return $attrs;
    }

    /**
     * Perform a model update operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return bool
     */
    protected function performUpdate(Builder $query)
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirtyForUpdate();

        if (count($dirty) > 0) {
            if (isset($dirty[static::getTranslationsAttrName()])) {
                $dirty = array_diff_key($dirty, array_flip([...$this->appends, 'translations']));
            }

            $this->setKeysForSaveQuery($query)->update($dirty);

            $this->syncChanges();

            $this->fireModelEvent('updated', false);
        }

        return true;
    }
}
