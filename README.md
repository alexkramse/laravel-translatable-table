# Laravel Translatable Table 

[![Latest Stable Version](https://img.shields.io/packagist/v/alexkramse/laravel-translatable-table.svg)](https://packagist.org/packages/alexkramse/laravel-translatable-table)
[![License](https://img.shields.io/github/license/alexkramse/laravel-translatable-table)](LICENSE)
[![Test](https://img.shields.io/github/actions/workflow/status/alexkramse/laravel-translatable-table/pest.yml?branch=main&label=code%20style)](https://github.com/alexkramse/laravel-translatable-table/actions/workflows/pest.yml)
[![Pint Check](https://img.shields.io/github/actions/workflow/status/alexkramse/laravel-translatable-table/pint.yml?branch=main&label=code%20style)](https://github.com/alexkramse/laravel-translatable-table/actions/workflows/pint.yml)

Laravel Translatable Table is a package designed to simplify handling translations stored in a separate table. It provides traits, casts, and utilities to manage and interact with translatable attributes seamlessly.

## Supported Laravel Versions
The package supports Laravel 8, 9, and 10.

## Installation
To install the package, use Composer:

```bash
composer require alexkramse/laravel-translatable-table
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag=config --provider="Alexkramse\LaravelTranslatableTable\TranslatableTableServiceProvider"
```

This will create a `config/table-translations.php` file, where you can define supported locales and configure translation settings.

## Usage

### Setting Up a Translatable Model

1. Use the `HasTranslatableTable` trait in your model.
2. Define the `translatableTableAttributes` method to specify which attributes are translatable.
3. Ensure your model has a relationship to a translation model.

Example:

```php
use Alexkramse\LaravelTranslatableTable\Traits\HasTranslatableTable;

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


### Creating a Translation Model

For each translatable model, you will need to create a corresponding translation model. For example, for the `Article` model, you will need to create a `ArticleTranslation` model like this:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleTranslation extends Model
{
    use HasFactory;

    protected $guarded = [];
}
```

This model should be associated with the `article_translations` table (or another name depending on your schema). It contains the translated fields and references the original model (in this case, `Article`).

### Migration for Translations

You will also need to create a migration for the `article_translations` table. Here's an example migration:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5)->nullable(false)->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['article_id', 'locale']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_translations');
    }
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

## Notes

This is the first version of the package. The README and code may contain some inaccuracies or incomplete features. If you encounter any issues or have suggestions for improvements, please feel free to open an issue or submit a pull request.

## Testing

To test the package functionality, include your tests using Laravel's testing tools or Pest framework.

## License

This package is open-sourced software licensed under the MIT license.
