<?php

declare(strict_types=1);

return \Rector\Config\RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withTypeCoverageLevel(20)
    ->withDeadCodeLevel(20)
    ->withCodeQualityLevel(20)
    // ->withPreparedSets(
    //     deadCode: true,
    //     codeQuality: true,
    //     codingStyle: true,
    //     typeDeclarations: true,
    // )
    // ->withSkip([
    //     \Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector::class,
    // ])
;
