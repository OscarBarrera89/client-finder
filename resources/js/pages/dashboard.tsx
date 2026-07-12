import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface ClientePoliza {
    id: number;
    numero_poliza: string;
    matricula: string | null;
    compania?: {
        nombre: string;
    } | null;
}

interface ClienteItem {
    id: number;
    nombre: string;
    identificacion: string;
    telefono: string | null;
    email: string | null;
    direccion: string | null;
    polizas?: ClientePoliza[];
}

interface DashboardProps {
    clientes: ClienteItem[];
    query?: string;
}

export default function Dashboard() {
    const { props } = usePage<{ clientes: ClienteItem[]; query?: string }>();
    const [search, setSearch] = useState(props.query ?? '');

    const clientes = useMemo(() => props.clientes ?? [], [props.clientes]);

    const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get('/dashboard', { q: search }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-xl font-semibold">Buscador de clientes</h1>
                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                Busca por nombre, DNI, matrícula o número de póliza.
                            </p>
                        </div>
                        <Link href={route('home')} className="text-sm font-medium text-sky-600 hover:text-sky-700">
                            Volver al inicio
                        </Link>
                    </div>

                    <form onSubmit={handleSubmit} className="flex flex-col gap-2 sm:flex-row">
                        <label className="sr-only" htmlFor="cliente-search">
                            Buscar cliente
                        </label>
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input
                                id="cliente-search"
                                name="q"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                className="w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pl-10 pr-3 text-sm outline-none ring-0 focus:border-sky-500 focus:bg-white"
                                placeholder="Ej. Ana García, 12345678Z o ABC123"
                            />
                        </div>
                        <button
                            type="submit"
                            className="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700"
                        >
                            Buscar
                        </button>
                    </form>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold">Resultados</h2>
                        <span className="text-sm text-slate-500 dark:text-slate-400">
                            {clientes.length} cliente{clientes.length === 1 ? '' : 's'}
                        </span>
                    </div>

                    {clientes.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-slate-300 p-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            No se encontraron clientes con ese criterio.
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {clientes.map((cliente) => (
                                <div key={cliente.id} className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                                    <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <h3 className="font-semibold text-slate-900 dark:text-slate-100">{cliente.nombre}</h3>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                {cliente.identificacion} · {cliente.telefono ?? 'Sin teléfono'}
                                            </p>
                                        </div>
                                        <div className="text-sm text-slate-600 dark:text-slate-400">
                                            {cliente.email ?? 'Sin email'}
                                        </div>
                                    </div>

                                    {cliente.polizas && cliente.polizas.length > 0 ? (
                                        <div className="mt-3 grid gap-2 md:grid-cols-2">
                                            {cliente.polizas.map((poliza) => (
                                                <div key={poliza.id} className="rounded-md bg-slate-50 p-3 text-sm dark:bg-slate-800/70">
                                                    <p className="font-medium text-slate-900 dark:text-slate-100">{poliza.numero_poliza}</p>
                                                    <p className="text-slate-600 dark:text-slate-400">
                                                        Matrícula: {poliza.matricula ?? 'Sin matrícula'}
                                                    </p>
                                                    <p className="text-slate-600 dark:text-slate-400">
                                                        Compañía: {poliza.compania?.nombre ?? 'Sin compañía'}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="mt-3 text-sm text-slate-500 dark:text-slate-400">Sin pólizas asociadas.</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
