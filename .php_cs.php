<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2'                       => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports'             => true,
        'phpdoc_order'                => true,
        'single_quote'                => false,
        'trim_array_spaces'           => false,
        'unary_operator_spaces'       => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/src')
    );
