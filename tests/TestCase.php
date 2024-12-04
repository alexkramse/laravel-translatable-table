<?php

namespace Alexkramse\LaravelTranslatableTable\Tests;

use Alexkramse\LaravelTranslatableTable\TranslatableTableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        //        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->beforeApplicationDestroyed(
            fn () => $this->artisan('migrate:rollback', ['--database' => 'testbench'])->run()
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            TranslatableTableServiceProvider::class,
        ];
    }
}
