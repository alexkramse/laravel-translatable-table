# Laravel Translatable Table

Laravel Translatable Table is a package designed to simplify handling translations stored in a separate table. It provides traits, casts, and utilities to manage and interact with translatable attributes seamlessly.

## Installation

To install the package, use Composer:

```bash
composer require akuadev/laravel-translatable-table
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag=config --provider="Akuadev\LaravelTranslatableTable\TranslatableTableServiceProvider"
```

This will create a `config/table-translations.php` file, where you can define supported locales and configure translation settings.

## Usage

### Setting Up a Translatable Model

1. Use the `HasTranslatableTable` trait in your model.
2. Define the `translatableTableAttributes` method to specify which attributes are translatable.
3. Ensure your model has a relationship to a translation model.

Example:

```php
use Akuadev\LaravelTranslatableTable\Traits\HasTranslatableTable;

class Post extends Model
{
use HasTranslatableTable;

    protected $fillable = ['slug', 'content'];

    public function translatableTableAttributes(): array
    {
        return ['title', 'description'];
    }
}
```

### Translation Model

The translation model should follow Laravel conventions, such as having a `post_id` foreign key and `locale` column. Example:

```php
class PostTranslation extends Model
{
    protected $fillable = ['title', 'description', 'locale', 'post_id'];
}
```

### Configuring Locales

Update the `config/table-translations.php` file to define the supported locales:

```php
return [
    'locales' => [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
    ],
    'translation_model_suffix' => 'Translation',
    'attribute_name' => 'i18n',
];
```

### Interacting with Translations

You can manage translations directly via the `i18n` attribute on the translatable model:

```php
$post = Post::create([
    'slug' => 'example-post',
    'content' => 'This is the content.',
    'i18n' => [
        'en' => ['title' => 'Example Post', 'description' => 'An example post description.'],
        'fr' => ['title' => 'Exemple de publication', 'description' => 'Une description de publication.'],
    ],
]);

// Access translations
$title = $post->title; // Retrieves the title based on the current locale
$translations = $post->i18n; // Retrieves all translations
```

## Advanced Features

- **Customizing Locales:** You can dynamically define available locales via the configuration file.
- **Eloquent Events:** Translations are automatically handled during `create` and `update` events.

## Testing

To test the package functionality, include your tests using Laravel's testing tools or Pest framework.

## License

This package is open-sourced software licensed under the MIT license.
