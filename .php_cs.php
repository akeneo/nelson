<?php
$fixers = require __DIR__ . '/.php_cs-fixers.php';
$finder = \Symfony\CS\Finder\DefaultFinder::create();
$finder->name('*.php')
    ->notName('*Spec.php')
    ->files()
    ->in(__DIR__);

return \Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers($fixers)
    ->finder($finder);
