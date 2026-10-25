import { useForm } from '@inertiajs/react';
import Campo, { claseBoton } from '@/components/Campo';
import PantallaAcceso from '@/components/PantallaAcceso';

export default function Login({ status }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <PantallaAcceso titulo="Iniciar sesión">
            {status && <p className="text-sm text-green-700 dark:text-green-400 text-center">{status}</p>}

            <form onSubmit={handleSubmit} className="space-y-5">
                <Campo
                    id="email"
                    label="Email"
                    type="email"
                    required
                    autoFocus
                    autoComplete="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />
                <Campo
                    id="password"
                    label="Contraseña"
                    type="password"
                    required
                    autoComplete="current-password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                />

                <label className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                    />
                    Recordarme
                </label>

                <button type="submit" disabled={processing} className={`w-full ${claseBoton}`}>
                    {processing ? 'Entrando…' : 'Entrar'}
                </button>

                <p className="text-xs text-gray-500 dark:text-gray-400 text-center">
                    ¿Has olvidado la contraseña? Pídele al administrador que te la cambie.
                </p>
            </form>
        </PantallaAcceso>
    );
}
