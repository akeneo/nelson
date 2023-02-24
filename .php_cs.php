<?php
$finder = PhpCsFixer\Finder::create();
$finder->name('*.php')
    ->notName('*Spec.php')
    ->files();

$config = new PhpCsFixer\Config();
return $config->setUsingCache(false)
    ->setRules([
        '@PSR2' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
    ])
    ->setFinder($finder);
