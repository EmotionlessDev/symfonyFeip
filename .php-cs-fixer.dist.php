<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setLineEnding("\n")
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'no_extra_blank_lines' => true,
        'phpdoc_no_package' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
            'import_constants' => false,
        ],
    ])
;
