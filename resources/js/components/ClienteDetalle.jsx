import { ArrowLeft, Calendar, Car, Euro, FileText, Mail, MapPin, Phone } from 'lucide-react';

function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleDateString('es-ES');
}

function formatearImporte(importe) {
    if (importe === null || importe === undefined) return 'N/A';
    return `${Number(importe).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;
}

function estadoColor(nombreEstado) {
    switch (nombreEstado) {
        case 'Activa':
            return 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300';
        case 'Pendiente':
            return 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300';
        case 'Anulada':
            return 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300';
        default:
            return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100';
    }
}

function TarjetaPolizaDetalle({ poliza }) {
    return (
        <div className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div className="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Nº de póliza</p>
                    <p className="text-lg font-bold text-gray-800 dark:text-gray-100">{poliza.numero_poliza}</p>
                </div>
                <span className={`rounded-full px-3 py-1 text-xs font-medium ${estadoColor(poliza.estado?.nombre)}`}>
                    {poliza.estado?.nombre || 'N/A'}
                </span>
            </div>

            <div className="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div>
                    <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Compañía</p>
                    <p className="font-medium text-gray-800 dark:text-gray-100">{poliza.compania?.nombre || 'N/A'}</p>
                </div>
                <div>
                    <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Ramo</p>
                    <p className="font-medium text-gray-800 dark:text-gray-100">{poliza.ramo?.nombre || 'N/A'}</p>
                </div>

                {poliza.matricula && (
                    <div className="flex items-center gap-2">
                        <Car size={16} className="text-gray-400 dark:text-gray-500" />
                        <div>
                            <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Matrícula</p>
                            <p className="font-medium text-gray-800 dark:text-gray-100">
                                {poliza.matricula}
                                {poliza.marca_modelo ? ` · ${poliza.marca_modelo}` : ''}
                            </p>
                        </div>
                    </div>
                )}

                <div className="flex items-center gap-2">
                    <Euro size={16} className="text-gray-400 dark:text-gray-500" />
                    <div>
                        <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Prima anual</p>
                        <p className="font-medium text-gray-800 dark:text-gray-100">{formatearImporte(poliza.prima_anual)}</p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Calendar size={16} className="text-gray-400 dark:text-gray-500" />
                    <div>
                        <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Contratación</p>
                        <p className="font-medium text-gray-800 dark:text-gray-100">{formatearFecha(poliza.fecha_contratacion)}</p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Calendar size={16} className="text-gray-400 dark:text-gray-500" />
                    <div>
                        <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Vencimiento</p>
                        <p className="font-medium text-gray-800 dark:text-gray-100">{formatearFecha(poliza.fecha_vencimiento)}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function ClienteDetalle({ cliente, onVolver }) {
    const polizas = cliente.polizas || [];

    return (
        <div className="mx-auto max-w-4xl">
            <button
                onClick={onVolver}
                className="mb-6 flex items-center gap-2 font-medium text-blue-800 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200"
            >
                <ArrowLeft size={18} />
                Volver a resultados
            </button>

            <div className="mb-6 overflow-hidden rounded-lg border border-gray-100 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div className="border-b border-gray-100 bg-gradient-to-r from-red-50 to-blue-50 px-6 py-5 dark:border-gray-700 dark:from-gray-800 dark:to-gray-700">
                    <div className="flex items-start justify-between">
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 dark:text-gray-100">{cliente.nombre}</h2>
                            {cliente.identificacion && <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">ID: {cliente.identificacion}</p>}
                        </div>
                        <div className="rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-900 dark:bg-blue-900/50 dark:text-blue-200">
                            #{cliente.id}
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2">
                    {cliente.email && (
                        <div className="flex items-center gap-3">
                            <Mail className="text-blue-800 dark:text-blue-300" size={20} />
                            <div>
                                <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Email</p>
                                <a
                                    href={`mailto:${cliente.email}`}
                                    className="font-medium text-blue-800 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200"
                                >
                                    {cliente.email}
                                </a>
                            </div>
                        </div>
                    )}

                    {cliente.telefono && (
                        <div className="flex items-center gap-3">
                            <Phone className="text-green-600 dark:text-green-400" size={20} />
                            <div>
                                <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Teléfono</p>
                                <a
                                    href={`tel:${cliente.telefono}`}
                                    className="font-medium text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                                >
                                    {cliente.telefono}
                                </a>
                            </div>
                        </div>
                    )}

                    {cliente.direccion && (
                        <div className="flex items-center gap-3 md:col-span-2">
                            <MapPin className="text-gray-400 dark:text-gray-500" size={20} />
                            <div>
                                <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Dirección</p>
                                <p className="font-medium text-gray-800 dark:text-gray-100">{cliente.direccion}</p>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <h3 className="mb-3 flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100">
                <FileText size={20} className="text-blue-600 dark:text-blue-400" />
                Pólizas ({polizas.length})
            </h3>

            {polizas.length > 0 ? (
                <div className="space-y-4">
                    {polizas.map((poliza) => (
                        <TarjetaPolizaDetalle key={poliza.id} poliza={poliza} />
                    ))}
                </div>
            ) : (
                <p className="rounded-lg border border-gray-100 bg-white py-6 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Este cliente no tiene pólizas registradas.
                </p>
            )}
        </div>
    );
}
