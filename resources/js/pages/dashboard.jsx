import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import BarraSuperior from '@/components/BarraSuperior';
import { claseBoton } from '@/components/Campo';

// Búsqueda en servidor (DashboardController). Es independiente del buscador de BuscadorPage.
export default function Dashboard({ clientes = [], query = '' }) {
    const [busqueda, setBusqueda] = useState(query);

    const handleSubmit = (e) => {
        e.preventDefault();
        router.get('/dashboard', { q: busqueda }, { preserveState: true });
    };

    return (
        <div className="min-h-screen bg-gradient-to-b from-gray-50 dark:from-gray-900 to-gray-100 dark:to-gray-950">
            <Head title="Dashboard" />
            <BarraSuperior />

            <main className="container mx-auto px-4 py-10 max-w-4xl space-y-6">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 p-6">
                    <h1 className="text-xl font-bold text-gray-800 dark:text-gray-100">Buscador de clientes</h1>
                    <p className="text-sm text-gray-500 dark:text-gray-400 mb-4">Busca por nombre, DNI, matrícula o número de póliza.</p>

                    <form onSubmit={handleSubmit} className="flex flex-col gap-2 sm:flex-row">
                        <label className="sr-only" htmlFor="cliente-search">
                            Buscar cliente
                        </label>
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                            <input
                                id="cliente-search"
                                name="q"
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                className="w-full rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 py-2 pl-10 pr-3 focus:border-blue-800 dark:focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-800 dark:focus:ring-blue-400"
                                placeholder="Nombre, DNI, matrícula o póliza"
                            />
                        </div>
                        <button type="submit" className={claseBoton}>
                            Buscar
                        </button>
                    </form>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 p-6">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-bold text-gray-800 dark:text-gray-100">Resultados</h2>
                        <span className="text-sm text-gray-500 dark:text-gray-400">
                            {clientes.length} cliente{clientes.length === 1 ? '' : 's'}
                        </span>
                    </div>

                    {clientes.length === 0 ? (
                        <p className="rounded-md border border-dashed border-gray-300 dark:border-gray-600 p-6 text-sm text-gray-500 dark:text-gray-400">
                            No se encontraron clientes con ese criterio.
                        </p>
                    ) : (
                        <div className="space-y-3">
                            {clientes.map((cliente) => (
                                <div key={cliente.id} className="rounded-md border border-gray-200 dark:border-gray-700 p-4">
                                    <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <h3 className="font-semibold text-gray-900 dark:text-gray-100">{cliente.nombre}</h3>
                                            <p className="text-sm text-gray-600 dark:text-gray-300">
                                                {cliente.identificacion} · {cliente.telefono ?? 'Sin teléfono'}
                                            </p>
                                        </div>
                                        <div className="text-sm text-gray-600 dark:text-gray-300">{cliente.email ?? 'Sin email'}</div>
                                    </div>

                                    {cliente.polizas?.length > 0 ? (
                                        <div className="mt-3 grid gap-2 md:grid-cols-2">
                                            {cliente.polizas.map((poliza) => (
                                                <div key={poliza.id} className="rounded-md bg-gray-50 dark:bg-gray-900 p-3 text-sm">
                                                    <p className="font-medium text-gray-900 dark:text-gray-100">{poliza.numero_poliza}</p>
                                                    <p className="text-gray-600 dark:text-gray-300">
                                                        Matrícula: {poliza.matricula ?? 'Sin matrícula'}
                                                    </p>
                                                    <p className="text-gray-600 dark:text-gray-300">
                                                        Compañía: {poliza.compania?.nombre ?? 'Sin compañía'}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="mt-3 text-sm text-gray-500 dark:text-gray-400">Sin pólizas asociadas.</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}
