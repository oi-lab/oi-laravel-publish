<?php

namespace OiLab\OiLaravelPublish;

use Illuminate\Support\ServiceProvider;
use OiLab\OiLaravelPublish\Console\Commands\InstallAiSkillCommand;
use OiLab\OiLaravelPublish\Console\Commands\InstallDataCommand;
use OiLab\OiLaravelPublish\Console\Commands\InstallSettingsCommand;
use OiLab\OiLaravelPublish\Console\Commands\MigratePropsCommand;
use OiLab\OiLaravelPublish\Support\PublishTemplateRegistry;
use OiLab\OiLaravelPublish\Support\SettingResolver;
use OiLab\OiLaravelPublish\Support\SettingsInstaller;

class OiLaravelPublishServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/oi-laravel-publish.php',
            'oi-laravel-publish'
        );

        $this->app->singleton(PublishTemplateRegistry::class, fn (): PublishTemplateRegistry => new PublishTemplateRegistry(
            config('oi-laravel-publish.templates', []),
        ));

        $this->app->singleton(SettingResolver::class);
        $this->app->singleton(SettingsInstaller::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallAiSkillCommand::class,
                InstallDataCommand::class,
                InstallSettingsCommand::class,
                MigratePropsCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/oi-laravel-publish.php' => config_path('oi-laravel-publish.php'),
            ], 'oi-laravel-publish-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'oi-laravel-publish-migrations');

            $this->publishes([
                __DIR__.'/../resources/stubs/ai-skill.md' => base_path('.claude/skills/oilab-laravel-publish/SKILL.md'),
            ], 'oi-laravel-publish-skill');
        }
    }
}
