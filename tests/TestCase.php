<?php

declare(strict_types=1);

namespace Tests;

use App\Modules\Roles\Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Roles and permissions exist in every database-backed test. */
    protected bool $seed = true;

    protected string $seeder = RolesSeeder::class;
}
