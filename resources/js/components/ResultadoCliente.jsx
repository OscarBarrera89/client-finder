import { FileText, Mail, Phone } from 'lucide-react';

export default function ResultadoCliente({ cliente, onVerDetalles }) {
    return (
        <div className="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-md transition-shadow hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
            {/* Header con nombre y estado */}
            <div className="border-b border-gray-100 bg-gradient-to-r from-red-50 to-blue-50 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-700">
                <div className="flex items-start justify-between">
                    <div>
                        <h3 className="text-xl font-bold text-gray-800 dark:text-gray-100">{cliente.nombre}</h3>
                        {cliente.identificacion && <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">ID: {cliente.identificacion}</p>}
                    </div>
                    <div className="rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-900 dark:bg-blue-900/50 dark:text-blue-200">
                        #{cliente.id}
                    </div>
                </div>
            </div>

            {/* Body con información */}
            <div className="px-6 py-4">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {/* Email */}
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

                    {/* Teléfono */}
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
                </div>

                {/* Información adicional */}
                {cliente.direccion && (
                    <div className="mt-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <div className="grid grid-cols-1 gap-4 text-sm">
                            <div>
                                <p className="text-xs text-gray-500 uppercase dark:text-gray-400">Dirección</p>
                                <p className="font-medium text-gray-800 dark:text-gray-100">{cliente.direccion}</p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Pólizas */}
                {cliente.polizas && cliente.polizas.length > 0 && (
                    <div className="mt-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <h4 className="mb-3 flex items-center gap-2 font-bold text-gray-800 dark:text-gray-100">
                            <FileText size={18} className="text-blue-600 dark:text-blue-400" />
                            Pólizas ({cliente.polizas.length})
                        </h4>
                        <div className="space-y-2">
                            {cliente.polizas.slice(0, 3).map((poliza) => (
                                <div
                                    key={poliza.id}
                                    className="flex items-center justify-between rounded border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900"
                                >
                                    <div>
                                        <p className="text-sm font-medium text-gray-800 dark:text-gray-100">Póliza #{poliza.numero_poliza}</p>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">Ramo: {poliza.ramo?.nombre || 'N/A'}</p>
                                    </div>
                                    <span className="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                        {poliza.estado?.nombre || 'Activa'}
                                    </span>
                                </div>
                            ))}
                            {cliente.polizas.length > 3 && (
                                <p className="py-2 text-center text-sm text-gray-500 dark:text-gray-400">+{cliente.polizas.length - 3} más...</p>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Footer con botones */}
            <div className="flex gap-3 border-t border-gray-100 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
                <button onClick={onVerDetalles} className="flex-1 rounded bg-red-600 py-2 font-medium text-white transition hover:bg-red-700">
                    Ver Detalles
                </button>
                <button
                    className="flex-1 rounded bg-gray-200 py-2 font-medium text-gray-800 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                    disabled
                >
                    Editar
                </button>
            </div>
        </div>
    );
}
