<?php

$config = (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'php_unit_method_casing' => false,
        'get_class_to_class_keyword' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude(__DIR__.'/vendor')
            ->name('*.php')
    )
;

return $config;
