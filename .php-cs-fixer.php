<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/tests',
        __DIR__ . '/resources',
        __DIR__ . '/Modules',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@PhpCsFixer' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'single_space_after_construct' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        // NOTE: 當原本的專案有很多IDE 自動產的 Header comment時，可以先打開快速刪除
        // 'header_comment' => ['header' => ''],
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'none'],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters'], 'after_heredoc' => false],
        'phpdoc_align' => ['align' => 'left', 'tags' => [ 'property', 'property-read']],
        'phpdoc_types_order' => [
            'sort_algorithm' => 'none',
            'null_adjustment' => 'none',
        ],
        'phpdoc_no_alias_tag' => [
            'replacements' => ['type' => 'var', 'link' => 'see'],
        ],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_summary'=> false,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'single_line_comment_style' => [
            'comment_types' => ['asterisk'],
        ],
        'not_operator_with_successor_space' => true,
        'new_with_braces' => ['anonymous_class' => false],
        'single_line_empty_body' => false,
    ])
    ->setFinder($finder);
