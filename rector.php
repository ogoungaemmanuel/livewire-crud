<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
    ]) // register single rule
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class
    ]) // here we can define, what prepared sets of rules will be applied
    // ->withPreparedSets(
    //     deadCode: true,
    //     codeQuality: true
    // )
    // uncomment to reach your current PHP version
     ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
