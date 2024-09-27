<?php

declare(strict_types=1);

use Lmc\CodingStandard\Sniffs\Naming\InterfaceNameSniff;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToParamTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withSets([
        __DIR__ . '/vendor/lmc/coding-standard/ecs.php',
    ])
    ->withSkip([
        SelfAccessorFixer::class => null,
        'SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff.ReferencedGeneralException' => ['tests/Exception/*.php'],
        'PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting\DocCommentSniff.TagsNotGrouped' => [
            'src/Immutable/Generic/ISeq.php',   // skip fixing the order of phpstan annotations
            'src/Immutable/Generic/Seq.php',   // skip fixing the order of phpstan annotations
        ],
        InterfaceNameSniff::class => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff.MissingAnyTypeHint' => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff.MissingAnyTypeHint' => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff.MissingAnyTypeHint' => null,
        PhpdocToParamTypeFixer::class => [
            'src/Immutable/Tuple.php',
        ],
    ]);
