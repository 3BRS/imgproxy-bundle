<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\PSR12\Sniffs\Classes\ClassInstantiationSniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
    ]);

    // PSR-12 rules
    $ecsConfig->rule(ClassInstantiationSniff::class);

    // Clean code rules
    $ecsConfig->rule(ArraySyntaxFixer::class);
    $ecsConfig->rule(NoUnusedImportsFixer::class);
    $ecsConfig->rule(OrderedClassElementsFixer::class);
    $ecsConfig->rule(NoEmptyPhpdocFixer::class);
    $ecsConfig->rule(NoSuperfluousPhpdocTagsFixer::class);
    $ecsConfig->rule(MethodChainingIndentationFixer::class);
    $ecsConfig->rule(NotOperatorWithSuccessorSpaceFixer::class);
};
