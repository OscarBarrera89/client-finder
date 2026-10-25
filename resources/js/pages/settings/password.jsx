import { useForm } from '@inertiajs/react';
import Campo, { claseBoton } from '@/components/Campo';
import LayoutAjustes from '@/components/LayoutAjustes';

export default function Password() {
    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put('/settings/password', {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errores) => {
                if (errores.password) {
                    reset('password', 'password_confirmation');
                }
                if (errores.current_password) {
                    reset('current_password');
                }
            },
        });
    };

    return (
        <LayoutAjustes titulo="Contraseña">
            <form onSubmit={handleSubmit} className="space-y-5">
                <div>
                    <h2 className="text-lg font-bold text-gray-800 dark:text-gray-100">Cambiar contraseña</h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400">Usa una contraseña larga que no uses en otras webs.</p>
                </div>

                <Campo
                    id="current_password"
                    label="Contraseña actual"
                    type="password"
                    autoComplete="current-password"
                    value={data.current_password}
                    onChange={(e) => setData('current_password', e.target.value)}
                    error={errors.current_password}
                />
                <Campo
                    id="password"
                    label="Nueva contraseña"
                    type="password"
                    autoComplete="new-password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                />
                <Campo
                    id="password_confirmation"
                    label="Repite la nueva contraseña"
                    type="password"
                    autoComplete="new-password"
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    error={errors.password_confirmation}
                />

                <div className="flex items-center gap-4">
                    <button type="submit" disabled={processing} className={claseBoton}>
                        Guardar contraseña
                    </button>
                    {recentlySuccessful && <p className="text-sm text-green-700 dark:text-green-400">Guardado</p>}
                </div>
            </form>
        </LayoutAjustes>
    );
}
