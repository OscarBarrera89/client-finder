<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestión de usuarios por parte del administrador (jefe).
 * Solo se crean empleados; el admin no puede modificarse ni desactivarse desde aquí.
 */
class UsuarioController extends Controller
{
    public function index(): Response
    {
        $usuarios = User::query()
            ->orderByRaw('rol = ? desc', [User::ROL_ADMIN])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol', 'activo', 'created_at']);

        return Inertia::render('admin/UsuariosPage', [
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);

        User::create([
            ...$datos,
            'rol' => User::ROL_EMPLEADO,
            'activo' => true,
        ]);

        return back()->with('exito', 'Usuario creado correctamente.');
    }

    public function actualizarPassword(Request $request, User $usuario): RedirectResponse
    {
        $this->asegurarEsEmpleado($usuario);

        $datos = $request->validate([
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);

        $usuario->update(['password' => $datos['password']]);

        return back()->with('exito', 'Contraseña actualizada.');
    }

    public function cambiarActivo(User $usuario): RedirectResponse
    {
        $this->asegurarEsEmpleado($usuario);

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('exito', $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    /**
     * Evita que el admin se modifique o se bloquee a sí mismo desde la pantalla.
     */
    private function asegurarEsEmpleado(User $usuario): void
    {
        abort_if($usuario->esAdmin(), 403);
    }
}
