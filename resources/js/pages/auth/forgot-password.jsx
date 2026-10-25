import { Link, useForm } from '@inertiajs/react';
import Campo, { claseBoton } from '@/components/Campo';
import PantallaAcceso from '@/components/PantallaAcceso';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <PantallaAcceso
            titulo="Recuperar contraseña"
            descripcion="Introduce tu email y te enviaremos un enlace para cambiar la contraseña"
        >
            {status && <p className="text-sm text-green-700 dark:text-green-400 text-center">{status}</p>}

            <form onSubmit={handleSubmit} className="space-y-5">
                <Campo
                    id="email"
                    label="Email"
                    type="email"
                    required
                    autoFocus
                    autoComplete="off"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />

                <button type="submit" disabled={processing} className={`w-full ${claseBoton}`}>
                    Enviar enlace
                </button>

                <p className="text-sm text-gray-500 dark:text-gray-400 text-center">
                    <Link href="/login" className="text-blue-800 dark:text-blue-300 hover:underline">
                        Volver a iniciar sesión
                    </Link>
                </p>
            </form>
        </PantallaAcceso>
    );
}
