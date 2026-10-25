import { claseBoton } from '@/components/Campo';
import PantallaAcceso from '@/components/PantallaAcceso';
import { Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/email/verification-notification');
    };

    return (
        <PantallaAcceso titulo="Verifica tu email" descripcion="Pulsa el enlace que te hemos enviado por email para verificar tu dirección.">
            {status === 'verification-link-sent' && (
                <p className="text-center text-sm text-green-700 dark:text-green-400">Te hemos enviado un nuevo enlace de verificación.</p>
            )}

            <form onSubmit={handleSubmit} className="space-y-4 text-center">
                <button type="submit" disabled={processing} className={`w-full ${claseBoton}`}>
                    Reenviar email de verificación
                </button>

                <Link href="/logout" method="post" as="button" className="text-sm text-gray-600 hover:underline dark:text-gray-300">
                    Cerrar sesión
                </Link>
            </form>
        </PantallaAcceso>
    );
}
