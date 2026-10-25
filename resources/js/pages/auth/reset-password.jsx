import { useForm } from '@inertiajs/react';
import Campo, { claseBoton } from '@/components/Campo';
import PantallaAcceso from '@/components/PantallaAcceso';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/reset-password', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <PantallaAcceso titulo="Nueva contraseña" descripcion="Escribe tu nueva contraseña">
            <form onSubmit={handleSubmit} className="space-y-5">
                <Campo id="email" label="Email" type="email" readOnly value={data.email} error={errors.email} />
                <Campo
                    id="password"
                    label="Contraseña"
                    type="password"
                    required
                    autoFocus
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
                    error={errors.password_confirmation}
                />

                <button type="submit" disabled={processing} className={`w-full ${claseBoton}`}>
                    Cambiar contraseña
                </button>
            </form>
        </PantallaAcceso>
    );
}
