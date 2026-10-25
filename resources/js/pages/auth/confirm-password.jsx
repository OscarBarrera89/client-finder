import Campo, { claseBoton } from '@/components/Campo';
import PantallaAcceso from '@/components/PantallaAcceso';
import { useForm } from '@inertiajs/react';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/confirm-password', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <PantallaAcceso titulo="Confirma tu contraseña" descripcion="Esta es una zona protegida. Confirma tu contraseña para continuar.">
            <form onSubmit={handleSubmit} className="space-y-5">
                <Campo
                    id="password"
                    label="Contraseña"
                    type="password"
                    required
                    autoFocus
                    autoComplete="current-password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                />

                <button type="submit" disabled={processing} className={`w-full ${claseBoton}`}>
                    Confirmar
                </button>
            </form>
        </PantallaAcceso>
    );
}
