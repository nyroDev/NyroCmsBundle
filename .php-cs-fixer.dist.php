<?php

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_alternative_syntax' => false,
        'single_quote' => true,
        'increment_style' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'array_indentation' => true,
        'compact_nullable_typehint' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => ['=>' => null],
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
            'import_constants' => true,
        ],
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
        ],
    ],)
;
