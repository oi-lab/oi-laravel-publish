<?php

namespace OiLab\OiLaravelPublish\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Copies the package's page and block props, style and enum classes into the
 * host application so a project can adapt them — add a block, add a page prop,
 * add a style slot, extend an enum — without forking the package.
 *
 * The copied files keep extending the package's `PropsData`, so `PropsCast`
 * still hydrates them once `templates.*.propsClass` points at the new classes.
 */
class InstallDataCommand extends Command
{
    protected $signature = 'publish:install-data
        {--namespace=App\\Publish : Root namespace for the published classes}
        {--path= : Destination directory (defaults to the namespace mapped under app/)}
        {--force : Overwrite files that already exist}';

    protected $description = 'Publish the page and block props, style and enum classes into the host application';

    /**
     * Enums that stay in the package: they describe the package's own contract,
     * not the presentation a project adapts.
     *
     * @var array<int, string>
     */
    private const RETAINED_ENUMS = ['PublishTemplateType.php'];

    public function handle(Filesystem $files): int
    {
        $namespace = trim((string) $this->option('namespace'), '\\');

        if (! $this->isValidNamespace($namespace)) {
            $this->error("Invalid namespace: {$namespace}");

            return self::FAILURE;
        }

        $target = $this->targetPath($namespace);
        $source = __DIR__.'/../../..';

        $copied = 0;
        $skipped = 0;

        foreach ($this->plan($files, $source) as [$file, $subdirectory]) {
            $destination = rtrim($target.'/'.$subdirectory, '/').'/'.basename($file);

            if ($files->exists($destination) && ! $this->option('force')) {
                $this->line("  <fg=yellow>skipped</> {$destination} (exists — pass --force to overwrite)");
                $skipped++;

                continue;
            }

            $files->ensureDirectoryExists(dirname($destination));
            $files->put($destination, $this->rewrite($files->get($file), $namespace));

            $this->line("  <fg=green>copied</>  {$destination}");
            $copied++;
        }

        $this->newLine();
        $this->info("Published {$copied} class(es)".($skipped > 0 ? ", skipped {$skipped}." : '.'));

        if ($copied > 0) {
            $this->printNextSteps($namespace);
        }

        return self::SUCCESS;
    }

    /**
     * The files to publish, each paired with its destination sub-directory.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function plan(Filesystem $files, string $source): array
    {
        $plan = [
            [$source.'/src/Data/CtaData.php', ''],
            [$source.'/src/Data/ParamData.php', ''],
        ];

        foreach (['Blocks', 'Pages', 'Styles'] as $directory) {
            foreach ($files->files($source.'/src/Data/'.$directory) as $file) {
                $plan[] = [$file->getPathname(), $directory];
            }
        }

        foreach ($files->files($source.'/src/Enums') as $file) {
            if (in_array($file->getFilename(), self::RETAINED_ENUMS, true)) {
                continue;
            }

            $plan[] = [$file->getPathname(), 'Enums'];
        }

        return $plan;
    }

    /**
     * Repoint the package namespaces at the host ones.
     *
     * `PropsData` is deliberately left alone: the published classes keep
     * extending the package's base class, which is what `PropsCast` checks.
     */
    private function rewrite(string $contents, string $namespace): string
    {
        $replacements = [
            'OiLab\\OiLaravelPublish\\Data\\Blocks' => $namespace.'\\Blocks',
            'OiLab\\OiLaravelPublish\\Data\\Pages' => $namespace.'\\Pages',
            'OiLab\\OiLaravelPublish\\Data\\Styles' => $namespace.'\\Styles',
            'OiLab\\OiLaravelPublish\\Data\\CtaData' => $namespace.'\\CtaData',
            'OiLab\\OiLaravelPublish\\Data\\ParamData' => $namespace.'\\ParamData',
            'OiLab\\OiLaravelPublish\\Enums' => $namespace.'\\Enums',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $contents);
    }

    /**
     * Map a namespace onto a directory, honouring an explicit --path.
     */
    private function targetPath(string $namespace): string
    {
        $path = $this->option('path');

        if (is_string($path) && $path !== '') {
            return rtrim($path, '/');
        }

        if (str_starts_with($namespace, 'App\\')) {
            return app_path(str_replace('\\', '/', substr($namespace, 4)));
        }

        return base_path(str_replace('\\', '/', $namespace));
    }

    private function isValidNamespace(string $namespace): bool
    {
        return $namespace !== ''
            && (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $namespace);
    }

    private function printNextSteps(string $namespace): void
    {
        $this->newLine();
        $this->comment('Two steps remain:');
        $this->line('  1. Point each <info>templates.*.propsClass</info> in config/oi-laravel-publish.php');
        $this->line("     at its <info>{$namespace}\\Blocks\\…</info> counterpart.");
        $this->line('  2. In config/oi-laravel-ts.php, replace');
        $this->line('     <info>OiLab\\OiLaravelPublish\\Data</info> with '."<info>{$namespace}</info> in <info>data_namespaces</info>.");
        $this->line('     Keeping both would abort generation on a short-name collision');
        $this->line('     (or map one side through <info>data_aliases</info>).');
    }
}
