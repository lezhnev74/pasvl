<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindKeyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamStringTypeFromSprintfUseRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // Target PHP 8.0 (the declared runtime floor); no PHP 8.1+/8.4-only rewrites.
    ->withPhpSets(php80: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
    )
    ->withSkip([
        // PHP 8.4 native functions would raise the runtime floor above 8.0.
        ForeachToArrayFindRector::class,
        ForeachToArrayFindKeyRector::class,
        // Unsound here: array keys reach these methods as int|string, but the
        // rule infers a bare `string` param type from sprintf() usage.
        AddParamStringTypeFromSprintfUseRector::class,
    ]);
