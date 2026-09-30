<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function createRoles(): void
    {
        Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }
}