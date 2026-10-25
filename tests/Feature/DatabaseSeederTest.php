<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('the seeder does not create example users outside local', function () {
    // En los tests APP_ENV=testing
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(0);
});

test('the seeder creates an admin and an employee in local and can run twice', function () {
    app()->detectEnvironment(fn () => 'local');

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(2)
        ->and(User::where('rol', User::ROL_ADMIN)->count())->toBe(1)
        ->and(User::where('rol', User::ROL_EMPLEADO)->count())->toBe(1);
});
