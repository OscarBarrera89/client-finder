import BarraSuperior from '@/components/BarraSuperior';
import { claseBoton, claseInput } from '@/components/Campo';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FileSpreadsheet } from 'lucide-react';
import { useMemo, useState } from 'react';

const NOMBRES_CAMPO = { nombre: 'Nombre', telefono: 'Teléfono', email: 'Email', direccion: 'Dirección' };

const claseBotonSecundario =
    'rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700';

const claseTarjeta = 'rounded-lg border border-gray-100 bg-white p-6 shadow-md dark:border-gray-700 dark:bg-gray-800';

function Aviso({ tipo, children }) {
    const colores =
        tipo === 'error'
            ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300'
            : 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300';

    return <div className={`rounded-md border px-4 py-3 ${colores}`}>{children}</div>;
}

function FormularioSubida() {
    const { data, setData, post, processing, errors } = useForm({ archivo: null });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/importacion', { forceFormData: true });
    };

    return (
        <form onSubmit={handleSubmit} className={claseTarjeta}>
            <h2 className="mb-2 text-lg font-bold text-gray-800 dark:text-gray-100">Subir Excel</h2>
            <p className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Primera hoja, con cabecera: Nombre, DNI / NIE / CIF, Nº de póliza, Compañía y Ramo (obligatorias); Matrícula, Prima anual, Fecha de
                contratación, Vencimiento, Teléfono, Email y Dirección (opcionales). Antes de guardar verás una simulación.
            </p>
            <label htmlFor="archivo" className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Archivo .xlsx (máximo 10 MB)
            </label>
            <input
                id="archivo"
                type="file"
                accept=".xlsx"
                required
                onChange={(e) => setData('archivo', e.target.files[0] ?? null)}
                className={claseInput}
            />
            {errors.archivo && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.archivo}</p>}
            <button type="submit" disabled={processing || !data.archivo} className={`mt-4 ${claseBoton}`}>
                {processing ? 'Leyendo el archivo…' : 'Simular importación'}
            </button>
        </form>
    );
}

function Resumen({ resumen }) {
    const datos = [
        ['Clientes nuevos', resumen.clientes_nuevos],
        ['Clientes existentes', resumen.clientes_existentes],
        ['Pólizas nuevas', resumen.polizas_nuevas],
        ['Pólizas coincidentes', resumen.polizas_coincidentes],
        ['Compañías nuevas', resumen.companias_nuevas],
        ['Ramos nuevos', resumen.ramos_nuevos],
        ['Tipos de vencimiento nuevos', resumen.tipos_vencimiento_nuevos],
        ['Avisos', resumen.avisos],
        ['Errores', resumen.errores],
    ];

    return (
        <div className="grid grid-cols-2 gap-3 md:grid-cols-3">
            {datos.map(([etiqueta, valor]) => (
                <div key={etiqueta} className="rounded-md border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                    <p className="text-xs text-gray-500 dark:text-gray-400">{etiqueta}</p>
                    <p
                        className={`text-2xl font-bold ${etiqueta === 'Errores' && valor > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-100'}`}
                    >
                        {valor}
                    </p>
                </div>
            ))}
        </div>
    );
}

function TablaErrores({ errores }) {
    return (
        <div className={claseTarjeta}>
            <h2 className="mb-1 text-lg font-bold text-red-700 dark:text-red-400">Errores ({errores.length})</h2>
            <p className="mb-4 text-sm text-gray-600 dark:text-gray-400">No se puede importar hasta corregirlos en el Excel y volver a subirlo.</p>
            <div className="max-h-96 overflow-auto">
                <table className="w-full text-left text-sm">
                    <thead className="text-gray-500 dark:text-gray-400">
                        <tr>
                            <th className="py-1 pr-4">Fila</th>
                            <th className="py-1 pr-4">Columna</th>
                            <th className="py-1">Motivo</th>
                        </tr>
                    </thead>
                    <tbody className="text-gray-800 dark:text-gray-200">
                        {errores.map((error, i) => (
                            <tr key={i} className="border-t border-gray-100 dark:border-gray-700">
                                <td className="py-1 pr-4">{error.fila}</td>
                                <td className="py-1 pr-4">{error.columna}</td>
                                <td className="py-1">{error.motivo}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function CatalogosNuevos({ catalogos }) {
    const grupos = [
        ['Compañías', catalogos.companias],
        ['Ramos', catalogos.ramos],
        ['Tipos de vencimiento', catalogos.tipos_vencimiento],
    ].filter(([, lista]) => lista.length > 0);

    if (grupos.length === 0) {
        return null;
    }

    return (
        <div className={claseTarjeta}>
            <h2 className="mb-2 text-lg font-bold text-gray-800 dark:text-gray-100">Se crearán en los catálogos</h2>
            {grupos.map(([etiqueta, lista]) => (
                <p key={etiqueta} className="text-sm text-gray-700 dark:text-gray-300">
                    <span className="font-medium">{etiqueta}:</span> {lista.join(', ')}
                </p>
            ))}
        </div>
    );
}

function TarjetaCliente({ cliente, aceptado, onCambiar }) {
    const existente = cliente.estado === 'existente';

    return (
        <li className="rounded-md border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div className="flex items-center gap-3 px-4 py-3">
                <input type="checkbox" checked={aceptado} onChange={onCambiar} aria-label={`Importar ${cliente.nombre}`} className="h-4 w-4" />
                <div className="flex-1">
                    <p className="font-medium text-gray-800 dark:text-gray-100">{cliente.nombre}</p>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        {cliente.identificacion} · {cliente.polizas_nuevas.length} pólizas nuevas
                        {existente && ` · ${cliente.polizas_coincidentes.length} coincidentes · ${cliente.diferencias.length} datos distintos`}
                    </p>
                </div>
                <span
                    className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                        existente
                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'
                    }`}
                >
                    {existente ? 'Ya existe' : 'Nuevo'}
                </span>
            </div>
            <details className="border-t border-gray-100 px-4 py-2 text-sm dark:border-gray-700">
                <summary className="cursor-pointer text-blue-800 dark:text-blue-300">Ver detalle</summary>
                <div className="space-y-3 py-2 text-gray-700 dark:text-gray-300">
                    {cliente.avisos.map((aviso, i) => (
                        <p key={i} className="text-amber-700 dark:text-amber-400">
                            ⚠ {aviso}
                        </p>
                    ))}
                    {cliente.diferencias.length > 0 && (
                        <div>
                            <p className="font-medium">Datos que se actualizarán:</p>
                            <ul className="list-disc pl-5">
                                {cliente.diferencias.map((d) => (
                                    <li key={d.campo}>
                                        {NOMBRES_CAMPO[d.campo]}: «{d.actual ?? '(vacío)'}» → «{d.nuevo}»
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                    {cliente.polizas_nuevas.length > 0 && (
                        <div>
                            <p className="font-medium">Pólizas nuevas:</p>
                            <ul className="list-disc pl-5">
                                {cliente.polizas_nuevas.map((p) => (
                                    <li key={p.numero_poliza}>
                                        {p.numero_poliza} · {p.compania} · {p.ramo}
                                        {p.matricula && ` · ${p.matricula}`}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                    {cliente.polizas_coincidentes.length > 0 && (
                        <p>
                            <span className="font-medium">Ya las tiene (no se tocan):</span> {cliente.polizas_coincidentes.join(', ')}
                        </p>
                    )}
                </div>
            </details>
        </li>
    );
}

function Simulacion({ simulacion, huella }) {
    const todos = useMemo(() => simulacion.clientes.map((c) => c.identificacion), [simulacion]);
    const [aceptados, setAceptados] = useState(() => new Set(todos));
    const [filtro, setFiltro] = useState('');
    const [enviando, setEnviando] = useState(false);
    const hayErrores = simulacion.errores.length > 0;

    const visibles = useMemo(() => {
        const texto = filtro.trim().toLowerCase();
        return texto
            ? simulacion.clientes.filter((c) => c.nombre.toLowerCase().includes(texto) || c.identificacion.toLowerCase().includes(texto))
            : simulacion.clientes;
    }, [simulacion, filtro]);

    const cambiar = (identificacion) =>
        setAceptados((actual) => {
            const nuevo = new Set(actual);
            if (nuevo.has(identificacion)) {
                nuevo.delete(identificacion);
            } else {
                nuevo.add(identificacion);
            }
            return nuevo;
        });

    const confirmar = () => {
        router.post(
            '/admin/importacion/confirmar',
            { aceptados: [...aceptados], huella },
            { onStart: () => setEnviando(true), onFinish: () => setEnviando(false) },
        );
    };

    const cancelar = () => router.delete('/admin/importacion');

    return (
        <>
            <Resumen resumen={simulacion.resumen} />
            {hayErrores && <TablaErrores errores={simulacion.errores} />}
            <CatalogosNuevos catalogos={simulacion.catalogos_nuevos} />

            {simulacion.clientes.length > 0 && (
                <div className={claseTarjeta}>
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h2 className="text-lg font-bold text-gray-800 dark:text-gray-100">
                            Clientes ({aceptados.size} de {todos.length} aceptados)
                        </h2>
                        <div className="flex gap-2">
                            <button type="button" onClick={() => setAceptados(new Set(todos))} className={claseBotonSecundario}>
                                Aceptar todos
                            </button>
                            <button type="button" onClick={() => setAceptados(new Set())} className={claseBotonSecundario}>
                                Descartar todos
                            </button>
                        </div>
                    </div>
                    <input
                        type="search"
                        placeholder="Filtrar por nombre o DNI / NIE / CIF"
                        value={filtro}
                        onChange={(e) => setFiltro(e.target.value)}
                        className={`mb-4 ${claseInput}`}
                    />
                    <ul className="space-y-2">
                        {visibles.map((cliente) => (
                            <TarjetaCliente
                                key={cliente.identificacion}
                                cliente={cliente}
                                aceptado={aceptados.has(cliente.identificacion)}
                                onCambiar={() => cambiar(cliente.identificacion)}
                            />
                        ))}
                    </ul>
                </div>
            )}

            <div className="flex flex-wrap gap-3">
                <button type="button" onClick={confirmar} disabled={hayErrores || aceptados.size === 0 || enviando} className={claseBoton}>
                    {enviando ? 'Importando…' : `Confirmar importación (${aceptados.size} clientes)`}
                </button>
                <button type="button" onClick={cancelar} disabled={enviando} className={claseBotonSecundario}>
                    Cancelar
                </button>
            </div>
        </>
    );
}

export default function ImportacionPage({ simulacion, huella, caducada }) {
    const { flash, errors } = usePage().props;

    return (
        <>
            <Head title="Importar Excel" />
            <BarraSuperior />

            <main className="container mx-auto max-w-5xl space-y-6 px-4 py-10">
                <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-gray-100">
                    <FileSpreadsheet /> Importar clientes y pólizas
                </h1>

                {flash?.exito && <Aviso tipo="exito">{flash.exito}</Aviso>}
                {errors?.importacion && <Aviso tipo="error">{errors.importacion}</Aviso>}
                {caducada && <Aviso tipo="error">La simulación ha caducado (más de una hora). Vuelve a subir el archivo.</Aviso>}

                {simulacion ? <Simulacion key={huella} simulacion={simulacion} huella={huella} /> : <FormularioSubida />}
            </main>
        </>
    );
}
