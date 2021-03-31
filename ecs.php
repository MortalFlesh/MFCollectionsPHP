<?php

declare(strict_types=1);

use Lmc\CodingStandard\Sniffs\Naming\InterfaceNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\OperatorSpacingSniff;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SKIP,
        [
            SelfAccessorFixer::class => ['src/Immutable/ITuple.php'],
            'SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff.ReferencedGeneralException' => ['tests/Exception/*.php'],
            InterfaceNameSniff::class => null,
            VoidReturnFixer::class => [
                'src/IMap.php',
                'src/ICollection.php',
                'src/IList.php',
            ],
            PhpdocNoEmptyReturnFixer::class => [
                'src/IMap.php',
                'src/ICollection.php',
                'src/IList.php',
            ],
            OperatorSpacingSniff::class => null,
            BinaryOperatorSpacesFixer::class => null,
        ]
    );

    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs.php');

    $services = $containerConfigurator->services();
    $services
        ->set(NoSuperfluousPhpdocTagsFixer::class)
        ->call('configure', [['allow_mixed' => true]]);
};
