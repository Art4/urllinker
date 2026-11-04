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
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
    )
    ->withComposerBased(
        phpunit: true,
    )
    ->withSkip([
        // A readonly class would turn creating a dynamic property on a UrlLinker from a
        // deprecation notice into an Error, which is a behavior change for callers.
        \Rector\Php82\Rector\Class_\ReadOnlyClassRector::class => [
            __DIR__ . '/src/UrlLinker.php',
        ],
        // The test asserts through expectUserDeprecationMessage(), which Rector does not
        // count; the attribute would make PHPUnit mark the test as risky.
        \Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector::class => [
            __DIR__ . '/tests/Unit/UrlLinkerTest.php',
        ],
    ])
;
