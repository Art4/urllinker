<?php

declare(strict_types=1);

return \Rector\Config\RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        earlyReturn: true,
        instanceOf: true,
        naming: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
    )
    ->withComposerBased(
        phpunit: true,
    )
;
