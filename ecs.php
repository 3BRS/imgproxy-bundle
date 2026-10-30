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

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withRules([
        // PSR-12 rules
        ClassInstantiationSniff::class,

        // Clean code rules
        ArraySyntaxFixer::class,
        NoUnusedImportsFixer::class,
        OrderedClassElementsFixer::class,
        NoEmptyPhpdocFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
        MethodChainingIndentationFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
    ]);
