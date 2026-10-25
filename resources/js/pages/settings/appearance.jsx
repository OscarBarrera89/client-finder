import LayoutAjustes from '@/components/LayoutAjustes';
import { router, usePage } from '@inertiajs/react';
import { Monitor, Moon, Sun } from 'lucide-react';

const OPCIONES = [
    { valor: 'claro', label: 'Claro', Icono: Sun },
    { valor: 'oscuro', label: 'Oscuro', Icono: Moon },
    { valor: 'sistema', label: 'Según el dispositivo', Icono: Monitor },
];

export default function Appearance() {
    const { auth } = usePage().props;
    const temaActual = auth.user.tema;

    const cambiarTema = (tema) => {
        router.patch('/settings/appearance', { tema }, { preserveScroll: true });
    };

    return (
        <LayoutAjustes titulo="Apariencia">
            <div className="space-y-5">
                <div>
                    <h2 className="text-lg font-bold text-gray-800 dark:text-gray-100">Tema</h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        Se guarda en tu cuenta y se aplica en cualquier dispositivo desde el que entres.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    {OPCIONES.map(({ valor, label, Icono }) => (
                        <button
                            key={valor}
                            type="button"
                            onClick={() => cambiarTema(valor)}
                            aria-pressed={temaActual === valor}
                            className={
                                temaActual === valor
                                    ? 'flex flex-col items-center gap-2 rounded-lg border-2 border-blue-800 bg-blue-50 px-4 py-4 text-sm font-medium text-blue-900 dark:border-blue-400 dark:bg-blue-900/30 dark:text-blue-200'
                                    : 'flex flex-col items-center gap-2 rounded-lg border-2 border-gray-200 px-4 py-4 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700'
                            }
                        >
                            <Icono size={24} />
                            {label}
                        </button>
                    ))}
                </div>
            </div>
        </LayoutAjustes>
    );
}
