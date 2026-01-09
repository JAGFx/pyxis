<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\Enum\Config\Defaults;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\CodeQuality\Rector\Class_\InlineClassRoutePrefixRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/*.php',
    ]);

    $rectorConfig->parallel(maxNumberOfProcess: (int) ($_ENV['RECTOR_MAX_PROCESS'] ?? Defaults::PARALLEL_MAX_NUMBER_OF_PROCESS));
    $rectorConfig->importNames();

    /// Sets
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::PHP_84,
    ]);

    // Symfony
    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');

    $rectorConfig->sets([
        SymfonySetList::SYMFONY_74,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);

    // Doctrine
    $rectorConfig->sets([
        DoctrineSetList::DOCTRINE_CODE_QUALITY
    ]);

    // PHPUnit
    $rectorConfig->sets([
        PHPUnitSetList::PHPUNIT_90,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ]);

    // PHPStan
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

    // Rules
    $rectorConfig->rules([
        ExplicitPublicClassMethodRector::class,
        ExplicitNullableParamTypeRector::class,
        ChangeIfElseValueAssignToEarlyReturnRector::class,
        RemoveAlwaysElseRector::class,
        ReturnEarlyIfVariableRector::class,
    ]);

    // Remove rules
    $rectorConfig->skip([
        ConsistentImplodeRector::class,
        NewlineAfterStatementRector::class,
        RemoveAlwaysTrueIfConditionRector::class,
        RenameParamToMatchTypeRector::class,
        RenameMethodRector::class,
        RenamePropertyToMatchTypeRector::class,
        RenameVariableToMatchMethodCallReturnTypeRector::class,
        InlineClassRoutePrefixRector::class,
        ChangeOrIfContinueToMultiContinueRector::class,
    ]);
};