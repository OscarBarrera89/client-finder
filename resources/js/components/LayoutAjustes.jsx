import BarraSuperior from '@/components/BarraSuperior';
import { Head, Link, usePage } from '@inertiajs/react';

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
        <div className="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
            <Head title={titulo} />
            <BarraSuperior />

            <main className="container mx-auto max-w-2xl space-y-6 px-4 py-10">
                <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-100">Mi cuenta</h1>

                <nav className="flex gap-2">
                    {SECCIONES.map((seccion) => (
                        <Link
                            key={seccion.href}
                            href={seccion.href}
                            className={
                                url.startsWith(seccion.href)
                                    ? 'rounded-md bg-blue-800 px-3 py-1.5 text-sm text-white'
                                    : 'rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                            }
                        >
                            {seccion.label}
                        </Link>
                    ))}
                </nav>

                <div className="rounded-lg border border-gray-100 bg-white p-6 shadow-md dark:border-gray-700 dark:bg-gray-800">{children}</div>
            </main>
        </div>
    );
}
