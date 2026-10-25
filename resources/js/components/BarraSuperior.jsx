import { Link, usePage } from '@inertiajs/react';
import { LogOut, Search, UserCog, Users } from 'lucide-react';

export default function BarraSuperior() {
    const { auth } = usePage().props;
    const usuario = auth?.user;

    if (!usuario) {
        return null;
    }

    return (
        <header className="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div className="container mx-auto flex items-center justify-between gap-4 px-4 py-3">
                <nav className="flex items-center gap-4 text-sm">
                    <Link href="/" className="flex items-center gap-1 text-gray-700 hover:text-blue-800 dark:text-gray-300 dark:hover:text-blue-300">
                        <Search size={16} /> Buscador
                    </Link>
                    {usuario.rol === 'admin' && (
                        <Link
                            href="/admin/usuarios"
                            className="flex items-center gap-1 text-gray-700 hover:text-blue-800 dark:text-gray-300 dark:hover:text-blue-300"
                        >
                            <Users size={16} /> Usuarios
                        </Link>
                    )}
                </nav>

                <div className="flex items-center gap-4 text-sm">
                    <Link
                        href="/settings/profile"
                        className="flex items-center gap-1 text-gray-700 hover:text-blue-800 dark:text-gray-300 dark:hover:text-blue-300"
                        title="Mi cuenta"
                    >
                        <UserCog size={16} />
                        <span className="hidden sm:inline">{usuario.name}</span>
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        className="flex items-center gap-1 text-gray-700 hover:text-red-700 dark:text-gray-300 dark:hover:text-red-400"
                    >
                        <LogOut size={16} /> Salir
                    </Link>
                </div>
            </div>
        </header>
    );
}
