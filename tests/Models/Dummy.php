<?php

namespace Alexkramse\LaravelTranslatableTable\Tests\Models;

use Alexkramse\LaravelTranslatableTable\Traits\HasTranslatableTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    use HasFactory;
    use HasTranslatableTable;

    protected $fillable = ['user_data'];
    public function translatableTableAttributes(): array
    {
        return ['title', 'description'];
    }
}
