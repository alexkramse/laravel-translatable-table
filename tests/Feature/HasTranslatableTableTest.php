<?php

use Alexkramse\LaravelTranslatableTable\Tests\Models\Dummy;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    $this->dummy = Dummy::create(['user_data' => 'Some user data']);
    $this->dummy->tableTranslations()->createMany([
        ['locale' => 'en', 'title' => 'English Title'],
        ['locale' => 'uk', 'title' => 'Ukrainian Title'],
    ]);
});

it('returns translated attribute for current locale', function () {
    App::setLocale('en');
    expect($this->dummy->title)->toBe('English Title');
});

it('falls back to default locale when translation is missing', function () {
    App::setLocale('aa'); // No translation
    expect($this->dummy->title)->toBe('English Title');
});

it('returns all translations in the translations attribute', function () {
    $translations = $this->dummy->translations;

    expect($translations)->toMatchArray([
        'en' => ['title' => 'English Title', 'description' => null],
        'uk' => ['title' => 'Ukrainian Title', 'description' => null],
    ]);
});

it('updates translations when setting translations attribute', function () {
    $this->dummy->user_data = 'test';
    $this->dummy->translations = [
        'en' => ['title' => 'Updated English Title'],
        'uk' => ['title' => 'Updated Ukrainian Title'],
    ];
    $this->dummy->save();
    //    $this->dummy->refresh();

    expect($this->dummy->title)->toBe('Updated English Title');
});

it('updates translations when setting translations attribute without change basic attributes', function () {
    $this->dummy->translations = [
        'en' => ['title' => 'Updated English Title'],
        'uk' => ['title' => 'Updated Ukrainian Title'],
    ];
    $this->dummy->save();
    //    $this->dummy->refresh();

    expect($this->dummy->title)->toBe('Updated English Title');
});
