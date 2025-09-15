<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude([
        __DIR__ . '/src/Shared/Command/ImportOlderDataCommand.php',
        __DIR__ . '/src/Shared/Command/OldBudgetSummaryCommand.php',
    ])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '=' => 'align_single_space_minimal',
            ],
        ],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'only_if_meta',
                'method' => 'one',
                'property' => 'only_if_meta',
                'trait_import' => 'none',
                'case' => 'none',
            ],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'heredoc_to_nowdoc' => false,
        'phpdoc_summary' => false,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => false,
        ],
        'nullable_type_declaration_for_default_null_value' => true,
    ])
    ->setFinder($finder);