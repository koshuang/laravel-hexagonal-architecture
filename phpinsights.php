<?php

declare(strict_types=1);

use PhpCsFixer\Fixer;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | This option controls the default preset that will be used by PHP Insights
    | to make your code reliable, simple, and clean. However, you can always
    | adjust the `Metrics` and `Insights` below in this configuration file.
    |
    | Supported: "default", "laravel", "symfony", "magento2", "drupal"
    |
    */

    'preset' => 'laravel',

    /*
    |--------------------------------------------------------------------------
    | IDE
    |--------------------------------------------------------------------------
    |
    | This options allow to add hyperlinks in your terminal to quickly open
    | files in your favorite IDE while browsing your PhpInsights report.
    |
    | Supported: "textmate", "macvim", "emacs", "sublime", "phpstorm",
    | "atom", "vscode".
    |
    | If you have another IDE that is not in this list but which provide an
    | url-handler, you could fill this config with a pattern like this:
    |
    | myide://open?url=file://%f&line=%l
    |
    */

    'ide' => null,

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may adjust all the various `Insights` that will be used by PHP
    | Insights. You can either add, remove or configure `Insights`. Keep in
    | mind, that all added `Insights` must belong to a specific `Metric`.
    |
    */

    'exclude' => [
        'build',
        'vendor',
        '_ide_helper.php',
    ],

    'add' => [
        //  ExampleMetric::class => [
        //      ExampleInsight::class,
        //  ]
    ],

    'remove' => [
        Fixer\Basic\BracesFixer::class,
        Fixer\ClassNotation\ClassDefinitionFixer::class,
        Fixer\Operator\NewWithBracesFixer::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineFunctions::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UselessOverridingMethodSniff::class,
        PHP_CodeSniffer\Standards\PSR1\Sniffs\Classes\ClassDeclarationSniff::class,
        SlevomatCodingStandard\Sniffs\Classes\ForbiddenPublicPropertySniff::class,
        SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowShortTernaryOperatorSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\Functions\UnusedParameterSniff::class,
    ],

    'config' => [
        Fixer\ArrayNotation\ArraySyntaxFixer::class => ['syntax' => 'short'],
        // NOTE: 當原本的專案有很多IDE 自動產的 Header comment時，可以先打開快速刪除
        // Fixer\Comment\HeaderCommentFixer::class => ['header' => ''],
        Fixer\Comment\SingleLineCommentSpacingFixer::class => [
            'comment_types' => ['asterisk'],
        ],
        Fixer\ControlStructure\YodaStyleFixer::class => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        Fixer\ControlStructure\TrailingCommaInMultilineFixer::class => [
            'elements' => ['arrays', 'arguments', 'parameters'],
            'after_heredoc' => false,
        ],
        Fixer\Import\GlobalNamespaceImportFixer::class => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        Fixer\Import\OrderedImportsFixer::class => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'none'],
        Fixer\LanguageConstruct\SingleSpaceAfterConstructFixer::class => true,
        Fixer\Phpdoc\PhpdocAlignFixer::class => ['align' => 'left', 'tags' => [ 'property', 'property-read']],
        Fixer\Phpdoc\PhpdocTypesOrderFixer::class => [
            'sort_algorithm' => 'none',
            'null_adjustment' => 'none',
        ],
        Fixer\Phpdoc\PhpdocNoAliasTagFixer::class => [
            'replacements' => ['type' => 'var', 'link' => 'see'],
        ],
        Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer::class => false,
        Fixer\Phpdoc\PhpdocSummaryFixer::class => false,
        Fixer\Phpdoc\PhpdocSeparationFixer::class => false,
        Fixer\PhpUnit\PhpUnitMethodCasingFixer::class => ['case' => 'snake_case'],
        Fixer\Operator\NotOperatorWithSuccessorSpaceFixer::class => true,
        Fixer\Operator\ConcatSpaceFixer::class => ['spacing' => 'one'],
        Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer::class => [
            'strategy' => 'no_multi_line',
        ],
        NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10,
        ],
        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 200,
            'absoluteLineLimit' => 200,
            'exclude' => [
                'app/Models',
                'lang',
            ],
        ],
        SlevomatCodingStandard\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 200,
            'absoluteLineLimit' => 200,
        ],
        SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff::class => [
            'maxLength' => 80,
        ],
        SlevomatCodingStandard\Sniffs\Commenting\UselessFunctionDocCommentSniff::class => [
            'traversableTypeHints' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    |
    | Here you may define a level you want to reach per `Insights` category.
    | When a score is lower than the minimum level defined, then an error
    | code will be returned. This is optional and individually defined.
    |
    */

    'requirements' => [
        'min-quality' => 80,
        'min-complexity' => 80,
        'min-architecture' => 80,
        'min-style' => 100,
//        'disable-security-check' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Threads
    |--------------------------------------------------------------------------
    |
    | Here you may adjust how many threads (core) PHPInsights can use to perform
    | the analyse. This is optional, don't provide it and the tool will guess
    | the max core number available. This accept null value or integer > 0.
    |
    */

    'threads' => null,

];
