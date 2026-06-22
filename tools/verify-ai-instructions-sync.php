<?php

declare(strict_types=1);

/**
 * Verifies that critical AI-facing policy invariants remain visible across adapters.
 */
$root = dirname(__DIR__);

$files = [
    'AGENTS.md',
    'CLAUDE.md',
    'ai/README.md',
    'ai/skills/USAGE.md',
    '.github/copilot-instructions.md',
    '.github/skills/repo-quality-foundation/SKILL.md',
    '.github/skills/crawl-pipeline-integrity/SKILL.md',
    '.github/skills/coverage-and-lint-guard/SKILL.md',
    '.github/skills/class-purpose-and-module-map/SKILL.md',
    '.cursor/rules/00-repo-quality-foundation.mdc',
];

$missingFiles = [];
$corpus = '';
$failures = [];

foreach ($files as $file) {
    $path = $root.'/'.$file;

    if (! is_file($path)) {
        $missingFiles[] = $file;

        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        $failures[] = 'Unreadable instruction file: '.$file;

        continue;
    }

    $corpus .= "\n\n--- ".$file." ---\n".$content;
}

$requiredPatterns = [
    'stop-and-ask behavior' => '/stop and ask/i',
    'Pint formatting authority' => '/Pint.*(?:wins|authority|formatting)/i',
    'PHPCS structural' => '/PHPCS.*structural/i',
    'develop and master Git flow' => '/develop.*master/is',
    'crawl entry non-negotiable' => '/FlickrService::connection/i',
    'no OAuth in package' => '/No OAuth in package/i',
    'laravel-config app profiles' => '/xflickr_app/i',
    'composer check command' => '/composer check/i',
    'coverage target' => '/95%.*coverage|coverage.*95%/i',
];

foreach ($requiredPatterns as $label => $pattern) {
    if (! preg_match($pattern, $corpus)) {
        $failures[] = 'Missing required policy marker: '.$label;
    }
}

if ($missingFiles !== []) {
    $failures[] = 'Missing instruction files: '.implode(', ', $missingFiles);
}

if ($failures !== []) {
    fwrite(STDERR, "AI instructions verification failed:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

fwrite(STDOUT, "AI instructions verification passed.\n");
