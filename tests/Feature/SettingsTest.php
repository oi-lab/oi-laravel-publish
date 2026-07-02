<?php

use OiLab\OiLaravelPublish\Contracts\SettingStore;
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

it('routes reads and writes through an explicitly configured store', function () {
    $store = new class implements SettingStore
    {
        /** @var array<string, string> */
        public array $values = [];

        public function isAvailable(): bool
        {
            return true;
        }

        public function get(string $key, ?string $default = null): ?string
        {
            return $this->values[$key] ?? $default;
        }

        public function has(string $key): bool
        {
            return array_key_exists($key, $this->values);
        }

        public function set(string $key, string $value): void
        {
            $this->values[$key] = $value;
        }
    };

    app()->instance('custom.store', $store);
    config()->set('oi-laravel-publish.settings.store', 'custom.store');

    $created = app(SettingsInstaller::class)->install();

    expect($created)->toBe([
        'PUBLISH.PAGE_DESCRIPTION_RENDERER',
        'PUBLISH.BLOCK_DESCRIPTION_RENDERER',
    ])
        ->and($store->values['PUBLISH.PAGE_DESCRIPTION_RENDERER'])->toBe('markdown')
        ->and(app(SettingResolver::class)->get('PUBLISH.PAGE_DESCRIPTION_RENDERER', 'fallback'))->toBe('markdown')
        ->and(Setting::count())->toBe(0);
});
