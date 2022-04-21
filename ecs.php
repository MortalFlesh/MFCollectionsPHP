<?php

declare(strict_types=1);

use Lmc\CodingStandard\Sniffs\Naming\InterfaceNameSniff;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToParamTypeFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SKIP,
        [
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
        ],
    );

    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs.php');
    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs-8.1.php');
};
