import { Head, Link, usePage } from '@inertiajs/react';
import BarraSuperior from '@/components/BarraSuperior';

const SECCIONES = [
    { href: '/settings/profile', label: 'Perfil' },
    { href: '/settings/password', label: 'Contraseña' },
    { href: '/settings/appearance', label: 'Apariencia' },
];

/**
 * Estructura de las páginas de "Mi cuenta".
 */
export default function LayoutAjustes({ titulo, children }) {
    const { url } = usePage();

    return (
        <div className="min-h-screen bg-gradient-to-b from-gray-50 dark:from-gray-900 to-gray-100 dark:to-gray-950">
            <Head title={titulo} />
            <BarraSuperior />

            <main className="container mx-auto px-4 py-10 max-w-2xl space-y-6">
                <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-100">Mi cuenta</h1>

                <nav className="flex gap-2">
                    {SECCIONES.map((seccion) => (
                        <Link
                            key={seccion.href}
                            href={seccion.href}
                            className={
                                url.startsWith(seccion.href)
                                    ? 'rounded-md bg-blue-800 px-3 py-1.5 text-sm text-white'
                                    : 'rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                            }
                        >
                            {seccion.label}
                        </Link>
                    ))}
                </nav>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 p-6">{children}</div>
            </main>
        </div>
    );
}
