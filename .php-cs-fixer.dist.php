<?php

$finder = (new PhpCsFixer\Finder())
    ->in('src')
    ->in('tests')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        'use_arrow_functions' => false,
        '@PHPUnit100Migration:risky' => true,
    ])
    ->setFinder($finder)
;
