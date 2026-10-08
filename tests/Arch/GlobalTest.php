<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use App\Shared\Exceptions\DomainException;
use App\Shared\Providers\ModuleServiceProvider;

arch('debugging functions are never committed')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'die', 'exit', 'ddd', 'dumps'])
    ->not->toBeUsed();

arch('application code uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('tests and tooling use strict types')
    ->expect(['Tests', 'Tools'])
    ->toUseStrictTypes();

arch('concrete classes are final')
    ->expect('App')
    ->classes()
    ->toBeFinal()
    ->ignoring([
        Controller::class,
        DomainException::class,
        ModuleServiceProvider::class,
    ]);

arch('env() is only called inside config files')
    ->expect('env')
    ->not->toBeUsed();

arch('security preset')
    ->preset()
    ->security()
    ->ignoring(['assert', 'Tools']);

arch('Shared never depends on a module')
    ->expect('App\Shared')
    ->not->toUse('App\Modules');
