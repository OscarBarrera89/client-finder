import BarraSuperior from '@/components/BarraSuperior';
import Campo, { claseBoton, claseInput } from '@/components/Campo';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

function FormularioNuevoUsuario() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/usuarios', {
            preserveScroll: true,
            onSuccess: () => reset(),
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <form onSubmit={handleSubmit} className="rounded-lg border border-gray-100 bg-white p-6 shadow-md dark:border-gray-700 dark:bg-gray-800">
            <h2 className="mb-4 text-lg font-bold text-gray-800 dark:text-gray-100">Nuevo empleado</h2>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <Campo id="name" label="Nombre" required value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} />
                <Campo
                    id="email"
                    label="Email"
                    type="email"
                    required
                    autoComplete="off"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />
                <Campo
                    id="password"
                    label="Contraseña inicial (mínimo 10 caracteres)"
                    type="password"
                    required
                    autoComplete="new-password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                />
                <Campo
                    id="password_confirmation"
                    label="Repite la contraseña"
                    type="password"
                    required
                    autoComplete="new-password"
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                />
            </div>
            <button type="submit" disabled={processing} className={`mt-4 ${claseBoton}`}>
                Crear usuario
            </button>
        </form>
    );
}

function FormularioPassword({ usuario, onCerrar }) {
    const { data, setData, put, processing, errors } = useForm({
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/admin/usuarios/${usuario.id}/password`, {
            preserveScroll: true,
            onSuccess: () => onCerrar(),
        });
    };

    return (
        <form onSubmit={handleSubmit} className="mt-3 grid grid-cols-1 items-start gap-3 md:grid-cols-3">
            <div>
                <input
                    type="password"
                    placeholder="Nueva contraseña"
                    required
                    autoComplete="new-password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    className={claseInput}
                />
                {errors.password && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.password}</p>}
            </div>
            <input
                type="password"
                placeholder="Repite la contraseña"
                required
                autoComplete="new-password"
                value={data.password_confirmation}
                onChange={(e) => setData('password_confirmation', e.target.value)}
                className={claseInput}
            />
            <div className="flex gap-2">
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-md bg-blue-800 px-3 py-2 text-sm font-medium text-white hover:bg-blue-900 disabled:opacity-60"
                >
                    Guardar
                </button>
                <button
                    type="button"
                    onClick={onCerrar}
                    className="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Cancelar
                </button>
            </div>
        </form>
    );
}

function FilaUsuario({ usuario }) {
    const [cambiandoPassword, setCambiandoPassword] = useState(false);
    const esAdmin = usuario.rol === 'admin';

    const cambiarActivo = () => {
        router.patch(`/admin/usuarios/${usuario.id}/activo`, {}, { preserveScroll: true });
    };

    return (
        <li className="px-6 py-4">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p className="font-medium text-gray-800 dark:text-gray-100">
                        {usuario.name}
                        {esAdmin && (
                            <span className="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-900 dark:bg-blue-900/50 dark:text-blue-200">
                                Administrador
                            </span>
                        )}
                        {!usuario.activo && (
                            <span className="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                Desactivado
                            </span>
                        )}
                    </p>
                    <p className="text-sm text-gray-500 dark:text-gray-400">{usuario.email}</p>
                </div>

                {!esAdmin && (
                    <div className="flex gap-2">
                        <button
                            type="button"
                            onClick={() => setCambiandoPassword(!cambiandoPassword)}
                            className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cambiar contraseña
                        </button>
                        <button
                            type="button"
                            onClick={cambiarActivo}
                            className={
                                usuario.activo
                                    ? 'rounded-md border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/30'
                                    : 'rounded-md border border-green-300 px-3 py-1.5 text-sm text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-900/30'
                            }
                        >
                            {usuario.activo ? 'Desactivar' : 'Activar'}
                        </button>
                    </div>
                )}
            </div>

            {cambiandoPassword && <FormularioPassword usuario={usuario} onCerrar={() => setCambiandoPassword(false)} />}
        </li>
    );
}

export default function UsuariosPage({ usuarios }) {
    const { flash } = usePage().props;

    return (
        <div className="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
            <Head title="Usuarios" />
            <BarraSuperior />

            <main className="container mx-auto max-w-4xl space-y-8 px-4 py-10">
                <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-100">Usuarios</h1>

                {flash?.exito && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                        {flash.exito}
                    </div>
                )}

                <FormularioNuevoUsuario />

                <div className="rounded-lg border border-gray-100 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <ul className="divide-y divide-gray-100 dark:divide-gray-700">
                        {usuarios.map((usuario) => (
                            <FilaUsuario key={usuario.id} usuario={usuario} />
                        ))}
                    </ul>
                </div>
            </main>
        </div>
    );
}
