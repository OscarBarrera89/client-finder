<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Crea el usuario administrador (jefe). Se usa una vez al instalar la aplicación;
 * el resto de usuarios se crean desde la pantalla de gestión.
 */
class CrearAdmin extends Command
{
    protected $signature = 'usuario:crear-admin';

    protected $description = 'Crea el usuario administrador de la aplicación';

    public function handle(): int
    {
        $datos = [
            'name' => $this->ask('Nombre'),
            'email' => strtolower((string) $this->ask('Email')),
            'password' => $this->secret('Contraseña (mínimo 12 caracteres)'),
            'password_confirmation' => $this->secret('Repite la contraseña'),
        ];

        $validador = Validator::make($datos, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol' => User::ROL_ADMIN,
            'activo' => true,
        ]);

        $this->info('Administrador creado correctamente.');

        return self::SUCCESS;
    }
}
