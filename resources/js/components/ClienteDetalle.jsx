import { Mail, Phone, MapPin, FileText, ArrowLeft, Car, Calendar, Euro } from 'lucide-react';

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
        <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <div className="flex items-start justify-between gap-3 mb-4">
                <div>
                    <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">Nº de póliza</p>
                    <p className="text-lg font-bold text-gray-800 dark:text-gray-100">{poliza.numero_poliza}</p>
                </div>
                <span className={`text-xs px-3 py-1 rounded-full font-medium ${estadoColor(poliza.estado?.nombre)}`}>
                    {poliza.estado?.nombre || 'N/A'}
                </span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p className="text-gray-500 dark:text-gray-400 text-xs uppercase">Compañía</p>
                    <p className="text-gray-800 dark:text-gray-100 font-medium">{poliza.compania?.nombre || 'N/A'}</p>
                </div>
                <div>
                    <p className="text-gray-500 dark:text-gray-400 text-xs uppercase">Ramo</p>
                    <p className="text-gray-800 dark:text-gray-100 font-medium">{poliza.ramo?.nombre || 'N/A'}</p>
                </div>

                {poliza.matricula && (
                    <div className="flex items-center gap-2">
                        <Car size={16} className="text-gray-400 dark:text-gray-500" />
                        <div>
                            <p className="text-gray-500 dark:text-gray-400 text-xs uppercase">Matrícula</p>
                            <p className="text-gray-800 dark:text-gray-100 font-medium">
                                {poliza.matricula}
                                {poliza.marca_modelo ? ` · ${poliza.marca_modelo}` : ''}
                            </p>
                        </div>
                    </div>
                )}

                <div className="flex items-center gap-2">
                    <Euro size={16} className="text-gray-400 dark:text-gray-500" />
                    <div>
                        <p className="text-gray-500 dark:text-gray-400 text-xs uppercase">Prima anual</p>
                        <p className="text-gray-800 dark:text-gray-100 font-medium">{formatearImporte(poliza.prima_anual)}</p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Calendar size={16} className="text-gray-400 dark:text-gray-500" />
                    <div>
                        <p className="text-gray-500 dark:text-gray-400 text-xs uppercase">Contratación</p>
                        <p className="text-gray-800 dark:text-gray-100 font-medium">{formatearFecha(poliza.fecha_contratacion)}</p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Calendar size={16} className="text-gray-400 dark:text-gray-500" />
                    <div>
                        <p className="text-gray-500 dark:text-gray-400 text-xs uppercase">Vencimiento</p>
                        <p className="text-gray-800 dark:text-gray-100 font-medium">{formatearFecha(poliza.fecha_vencimiento)}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function ClienteDetalle({ cliente, onVolver }) {
    const polizas = cliente.polizas || [];

    return (
        <div className="max-w-4xl mx-auto">
            <button
                onClick={onVolver}
                className="flex items-center gap-2 text-blue-800 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-200 font-medium mb-6"
            >
                <ArrowLeft size={18} />
                Volver a resultados
            </button>

            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
                <div className="bg-gradient-to-r from-red-50 dark:from-gray-800 to-blue-50 dark:to-gray-700 px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div className="flex items-start justify-between">
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 dark:text-gray-100">{cliente.nombre}</h2>
                            {cliente.identificacion && (
                                <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">ID: {cliente.identificacion}</p>
                            )}
                        </div>
                        <div className="bg-blue-100 dark:bg-blue-900/50 text-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-medium">
                            #{cliente.id}
                        </div>
                    </div>
                </div>

                <div className="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    {cliente.email && (
                        <div className="flex items-center gap-3">
                            <Mail className="text-blue-800 dark:text-blue-300" size={20} />
                            <div>
                                <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">Email</p>
                                <a href={`mailto:${cliente.email}`} className="text-blue-800 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-200 font-medium">
                                    {cliente.email}
                                </a>
                            </div>
                        </div>
                    )}

                    {cliente.telefono && (
                        <div className="flex items-center gap-3">
                            <Phone className="text-green-600 dark:text-green-400" size={20} />
                            <div>
                                <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">Teléfono</p>
                                <a href={`tel:${cliente.telefono}`} className="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium">
                                    {cliente.telefono}
                                </a>
                            </div>
                        </div>
                    )}

                    {cliente.direccion && (
                        <div className="flex items-center gap-3 md:col-span-2">
                            <MapPin className="text-gray-400 dark:text-gray-500" size={20} />
                            <div>
                                <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">Dirección</p>
                                <p className="text-gray-800 dark:text-gray-100 font-medium">{cliente.direccion}</p>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <h3 className="font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2 text-lg">
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
                <p className="text-gray-500 dark:text-gray-400 py-6 text-center bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                    Este cliente no tiene pólizas registradas.
                </p>
            )}
        </div>
    );
}
