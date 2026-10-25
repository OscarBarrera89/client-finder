<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Usuarios de ejemplo para desarrollo. Datos ficticios; nunca se crean fuera de local.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            $this->command?->warn('Los usuarios de ejemplo solo se crean en local (APP_ENV=local).');

            return;
        }

        $usuarios = [
            [
                'name' => 'Admin Ejemplo',
                'email' => 'admin@example.com',
                'rol' => User::ROL_ADMIN,
            ],
            [
                'name' => 'Empleado Ejemplo',
                'email' => 'empleado@example.com',
                'rol' => User::ROL_EMPLEADO,
            ],
        ];

        foreach ($usuarios as $usuario) {
            // firstOrCreate: se puede ejecutar varias veces sin duplicar ni cambiar contraseñas
            User::firstOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'rol' => $usuario['rol'],
                    'password' => 'contrasena-de-prueba',
                    'activo' => true,
                ],
            );
        }

        $this->command?->info('Usuarios de ejemplo listos (ver database/seeders/DatabaseSeeder.php).');
    }
}
