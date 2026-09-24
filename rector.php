<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSkip([
        __DIR__ . '/src/*/Tests/*',
        __DIR__ . '/vendor',
    ])
    ->withPhpLevel(\Rector\ValueObject\PhpVersion::PHP_81)
    ->withSets([
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::CODE_QUALITY,
    ]);