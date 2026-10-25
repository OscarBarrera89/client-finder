import Campo, { claseBoton } from '@/components/Campo';
import LayoutAjustes from '@/components/LayoutAjustes';
import { useForm, usePage } from '@inertiajs/react';

// La cuenta no se puede borrar desde aquí: solo el administrador puede desactivarla.
export default function Profile() {
    const { auth } = usePage().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: auth.user.name,
        email: auth.user.email,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        patch('/settings/profile', { preserveScroll: true });
    };

    return (
        <LayoutAjustes titulo="Perfil">
            <form onSubmit={handleSubmit} className="space-y-5">
                <h2 className="text-lg font-bold text-gray-800 dark:text-gray-100">Datos personales</h2>

                <Campo
                    id="name"
                    label="Nombre"
                    required
                    autoComplete="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    error={errors.name}
                />
                <Campo
                    id="email"
                    label="Email"
                    type="email"
                    required
                    autoComplete="username"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />

                <div className="flex items-center gap-4">
                    <button type="submit" disabled={processing} className={claseBoton}>
                        Guardar
                    </button>
                    {recentlySuccessful && <p className="text-sm text-green-700 dark:text-green-400">Guardado</p>}
                </div>
            </form>
        </LayoutAjustes>
    );
}
