<?php

namespace OiLab\OiLaravelPublish\Tests;

use Illuminate\Foundation\Application;
use OiLab\OiLaravelAttachments\OiLaravelAttachmentsServiceProvider;
use OiLab\OiLaravelPublish\OiLaravelPublishServiceProvider;
use OiLab\OiLaravelPublish\Tests\Fixtures\Setting;
use OiLab\OiLaravelPublish\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            OiLaravelAttachmentsServiceProvider::class,
            OiLaravelPublishServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('oi-laravel-publish.user_model', User::class);
        $app['config']->set('oi-laravel-attachments.user_model', User::class);
        $app['config']->set('oi-laravel-publish.settings.model', Setting::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/database/migrations');
    }
}
