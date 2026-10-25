import { Head } from '@inertiajs/react';

/**
 * Pantalla centrada con el logo, para login y demás páginas de acceso.
 */
export default function PantallaAcceso({ titulo, descripcion, children }) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-gradient-to-b from-gray-50 to-gray-100 px-4 dark:from-gray-900 dark:to-gray-950">
            <Head title={titulo} />

            <div className="w-full max-w-sm">
                <div className="mb-8 flex justify-center">
                    <img
                        src="/images/logo-cordoba-gestion.jpeg"
                        alt="Córdoba Gestión"
                        className="h-20 w-auto dark:rounded-lg dark:bg-white dark:p-2"
                    />
                </div>

                <div className="space-y-5 rounded-lg border border-gray-100 bg-white p-6 shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div className="text-center">
                        <h1 className="text-xl font-bold text-gray-800 dark:text-gray-100">{titulo}</h1>
                        {descripcion && <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{descripcion}</p>}
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
