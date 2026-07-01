<?php

namespace OiLab\OiLaravelPublish\Console\Commands;

use Illuminate\Console\Command;
use OiLab\OiLaravelPublish\Support\SettingsInstaller;

class InstallSettingsCommand extends Command
{
    protected $signature = 'publish:install-settings';

    protected $description = 'Seed default publish settings into the host application Setting model when present';

    public function handle(SettingsInstaller $installer): int
    {
        if (! $installer->canInstall()) {
            $this->warn('No usable Setting model found — skipping publish settings installation.');

            return self::SUCCESS;
        }

        $created = $installer->install();

        if ($created === []) {
            $this->info('All publish settings are already present. Nothing to do.');

            return self::SUCCESS;
        }

        foreach ($created as $key) {
            $this->line("Created setting: <info>{$key}</info>");
        }

        $this->info(sprintf('Installed %d publish setting(s).', count($created)));

        return self::SUCCESS;
    }
}
