<?php

use OiLab\OiLaravelPublish\OiLaravelPublish;
use OiLab\OiLaravelPublish\Support\SettingResolver;
use OiLab\OiLaravelPublish\Support\SettingsInstaller;
use OiLab\OiLaravelPublish\Tests\Fixtures\Setting;

it('installs the default publish settings', function () {
    $created = app(SettingsInstaller::class)->install();

    expect($created)->toBe([
        'PUBLISH.PAGE_DESCRIPTION_RENDERER',
        'PUBLISH.BLOCK_DESCRIPTION_RENDERER',
    ])->and(Setting::count())->toBe(2);
});

it('is idempotent on a second install', function () {
    $installer = app(SettingsInstaller::class);
    $installer->install();

    expect($installer->install())->toBe([])
        ->and(Setting::count())->toBe(2);
});

it('installs settings through the artisan command', function () {
    $this->artisan('publish:install-settings')->assertSuccessful();

    expect(Setting::count())->toBe(2);
});

it('no-ops when no Setting model is available', function () {
    config()->set('oi-laravel-publish.settings.model', 'App\\Models\\Missing');

    expect(app(SettingsInstaller::class)->canInstall())->toBeFalse()
        ->and(app(SettingsInstaller::class)->install())->toBe([]);
});

it('falls back to the markdown renderer default', function () {
    expect(OiLaravelPublish::pageDescriptionRenderer())->toBe('markdown')
        ->and(OiLaravelPublish::blockDescriptionRenderer())->toBe('markdown');
});

it('reads the renderer from the Setting model when present', function () {
    Setting::create(['key' => 'PUBLISH.PAGE_DESCRIPTION_RENDERER', 'value' => 'html']);

    expect(app(SettingResolver::class)->get('PUBLISH.PAGE_DESCRIPTION_RENDERER', 'markdown'))->toBe('html');
});
