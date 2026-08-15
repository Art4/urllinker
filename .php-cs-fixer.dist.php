<?php

declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in('src')
    ->in('tests')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setRules([
        '@PER-CS3x0' => true,
        '@PER-CS3x0:risky' => true,
        '@PHP8x2Migration' => true,
        '@PHP8x2Migration:risky' => true,
        '@PHPUnit10x0Migration:risky' => true,
        'declare_strict_types' => true,
        'fully_qualified_strict_types' => true,
        'no_unused_imports' => true,
    ])
;
