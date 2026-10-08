<?php

declare(strict_types=1);

arch('debugging functions are never committed')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'die', 'exit', 'ddd'])
    ->not->toBeUsed();

arch('application code uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('env() is only called inside config files')
    ->expect('env')
    ->not->toBeUsed()
    ->ignoring('config');

arch('security preset')->preset()->security()->ignoring('assert');
