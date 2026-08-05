<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->target = sys_get_temp_dir().'/oi-publish-data-'.bin2hex(random_bytes(4));
});

afterEach(function () {
    File::deleteDirectory($this->target);
});

function installData(string $target, array $options = []): int
{
    return Artisan::call('publish:install-data', [
        '--path' => $target,
        ...$options,
    ]);
}

it('copies the blocks, pages, styles, enums, CtaData and ParamData into the target', function () {
    installData($this->target);

    expect(File::exists($this->target.'/CtaData.php'))->toBeTrue()
        ->and(File::exists($this->target.'/ParamData.php'))->toBeTrue()
        ->and(File::exists($this->target.'/Pages/PagePropsData.php'))->toBeTrue()
        ->and(File::exists($this->target.'/Blocks/HeroData.php'))->toBeTrue()
        ->and(File::exists($this->target.'/Styles/HeroStylesData.php'))->toBeTrue()
        ->and(File::exists($this->target.'/Enums/CtaVariant.php'))->toBeTrue();
});

it('leaves the package-owned PublishTemplateType enum behind', function () {
    installData($this->target);

    expect(File::exists($this->target.'/Enums/PublishTemplateType.php'))->toBeFalse();
});

it('repoints the namespaces at the host, keeping PropsData in the package', function () {
    installData($this->target);

    $hero = File::get($this->target.'/Blocks/HeroData.php');

    expect($hero)->toContain('namespace App\Publish\Blocks;')
        ->and($hero)->toContain('use App\Publish\CtaData;')
        ->and($hero)->toContain('use App\Publish\Styles\HeroStylesData;')
        // The base class stays in the package: PropsCast checks against it.
        ->and($hero)->toContain('use OiLab\OiLaravelPublish\Data\PropsData;')
        ->and($hero)->not->toContain('OiLab\OiLaravelPublish\Data\Blocks');
});

it('honours a custom namespace', function () {
    installData($this->target, ['--namespace' => 'Acme\Cms']);

    expect(File::get($this->target.'/Styles/HeroStylesData.php'))
        ->toContain('namespace Acme\Cms\Styles;')
        ->and(File::get($this->target.'/Blocks/HeroData.php'))
        ->toContain('use Acme\Cms\Styles\HeroStylesData;');
});

it('produces syntactically valid PHP', function () {
    installData($this->target);

    foreach (File::allFiles($this->target) as $file) {
        $output = [];
        $status = 0;
        exec('php -l '.escapeshellarg($file->getPathname()).' 2>&1', $output, $status);

        expect($status)->toBe(0, "Syntax error in {$file->getFilename()}: ".implode("\n", $output));
    }
});

it('refuses to overwrite without --force, and overwrites with it', function () {
    installData($this->target);
    File::put($this->target.'/Blocks/HeroData.php', '<?php // edited by hand');

    installData($this->target);
    expect(File::get($this->target.'/Blocks/HeroData.php'))->toBe('<?php // edited by hand');

    installData($this->target, ['--force' => true]);
    expect(File::get($this->target.'/Blocks/HeroData.php'))->toContain('class HeroData');
});

it('rejects an invalid namespace', function () {
    $status = installData($this->target, ['--namespace' => '9Bad\\Namespace']);

    expect($status)->toBe(1)
        ->and(File::isDirectory($this->target))->toBeFalse();
});
