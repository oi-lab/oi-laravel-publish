<?php

/**
 * Sync the canonical skill stubs to all AI assistant skill directories.
 * Run via: composer sync-ai-skills
 */
$root = dirname(__DIR__);

$skills = [
    'oilab-laravel-publish' => 'resources/stubs/ai-skill.md',
    'oi-publish-add-block' => 'resources/stubs/add-block-skill.md',
];

$assistants = ['.claude/skills', '.junie/skills'];

$status = 0;

foreach ($skills as $skill => $stubPath) {
    $stub = $root.'/'.$stubPath;

    if (! is_file($stub)) {
        fwrite(STDERR, "Stub not found: {$stub}".PHP_EOL);
        $status = 1;

        continue;
    }

    foreach ($assistants as $assistant) {
        $target = $root.'/'.$assistant.'/'.$skill.'/SKILL.md';
        $dir = dirname($target);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        copy($stub, $target);
        echo 'Synced: '.str_replace($root.'/', '', $target).PHP_EOL;
    }
}

exit($status);
