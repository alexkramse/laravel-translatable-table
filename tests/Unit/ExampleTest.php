<?php


use Akuadev\LaravelTranslatableTable\Tests\Models\Dummy;

test('article should be created on three languages', function () {
    $dummy = Dummy::create([
        'i18n' => [
            'en' => [
                'title' => 'EN Title',
                'description' => 'EN description',
            ],
            'uk' => [
                'title' => 'UK Title',
                'description' => 'UK description',
            ],
            'de' => [
                'title' => 'DE Title',
                'description' => 'DE description',
            ],
        ],
    ]);
    expect($dummy->i18n['en']['title'])->toBeTrue('EN Title');
});
