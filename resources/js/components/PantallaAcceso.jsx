import { Head } from '@inertiajs/react';

/**
 * Pantalla centrada con el logo, para login y demás páginas de acceso.
 */
export default function PantallaAcceso({ titulo, descripcion, children }) {
    return (
        <div className="min-h-screen bg-gradient-to-b from-gray-50 dark:from-gray-900 to-gray-100 dark:to-gray-950 flex items-center justify-center px-4">
            <Head title={titulo} />

            <div className="w-full max-w-sm">
                <div className="flex justify-center mb-8">
                    <img
                        src="/images/logo-cordoba-gestion.jpeg"
                        alt="Córdoba Gestión"
                        className="h-20 w-auto dark:rounded-lg dark:bg-white dark:p-2"
                    />
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-5">
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
