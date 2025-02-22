<?php

$config = (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());

return $config->setRules([
    '@Symfony' => true,
    'array_syntax' => ['syntax' => 'short'],
    'no_alternative_syntax' => false,
    'single_quote' => true,
    'increment_style' => false,
    'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
    'array_indentation' => true,
    'compact_nullable_type_declaration' => true,
    'binary_operator_spaces' => [
        'default' => 'single_space',
    ],
    'global_namespace_import' => [
        'import_classes' => true,
        'import_functions' => true,
        'import_constants' => true,
    ],
    'ordered_imports' => [
        'imports_order' => ['class', 'function', 'const'],
    ],
],
)
;
