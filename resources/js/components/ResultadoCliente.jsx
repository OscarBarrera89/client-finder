import { Mail, Phone, FileText } from 'lucide-react';

export default function ResultadoCliente({ cliente, onVerDetalles }) {
    return (
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
            {/* Header con nombre y estado */}
            <div className="bg-gradient-to-r from-red-50 dark:from-gray-800 to-blue-50 dark:to-gray-700 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div className="flex items-start justify-between">
                    <div>
                        <h3 className="text-xl font-bold text-gray-800 dark:text-gray-100">
                            {cliente.nombre}
                        </h3>
                        {cliente.identificacion && (
                            <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                ID: {cliente.identificacion}
                            </p>
                        )}
                    </div>
                    <div className="bg-blue-100 dark:bg-blue-900/50 text-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-medium">
                        #{cliente.id}
                    </div>
                </div>
            </div>

            {/* Body con información */}
            <div className="px-6 py-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Email */}
                    {cliente.email && (
                        <div className="flex items-center gap-3">
                            <Mail className="text-blue-800 dark:text-blue-300" size={20} />
                            <div>
                                <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">Email</p>
                                <a
                                    href={`mailto:${cliente.email}`}
                                    className="text-blue-800 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-200 font-medium"
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
                                <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">Teléfono</p>
                                <a 
                                    href={`tel:${cliente.telefono}`}
                                    className="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium"
                                >
                                    {cliente.telefono}
                                </a>
                            </div>
                        </div>
                    )}
                </div>

                {/* Información adicional */}
                {cliente.direccion && (
                    <div className="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div className="grid grid-cols-1 gap-4 text-sm">
                            <div>
                                <p className="text-gray-500 dark:text-gray-400 text-xs uppercase">Dirección</p>
                                <p className="text-gray-800 dark:text-gray-100 font-medium">{cliente.direccion}</p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Pólizas */}
                {cliente.polizas && cliente.polizas.length > 0 && (
                    <div className="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <h4 className="font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <FileText size={18} className="text-blue-600 dark:text-blue-400" />
                            Pólizas ({cliente.polizas.length})
                        </h4>
                        <div className="space-y-2">
                            {cliente.polizas.slice(0, 3).map((poliza) => (
                                <div 
                                    key={poliza.id}
                                    className="bg-gray-50 dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700 flex justify-between items-center"
                                >
                                    <div>
                                        <p className="text-sm font-medium text-gray-800 dark:text-gray-100">
                                            Póliza #{poliza.numero_poliza}
                                        </p>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                            Ramo: {poliza.ramo?.nombre || 'N/A'}
                                        </p>
                                    </div>
                                    <span className="text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 px-2 py-1 rounded">
                                        {poliza.estado?.nombre || 'Activa'}
                                    </span>
                                </div>
                            ))}
                            {cliente.polizas.length > 3 && (
                                <p className="text-sm text-gray-500 dark:text-gray-400 text-center py-2">
                                    +{cliente.polizas.length - 3} más...
                                </p>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Footer con botones */}
            <div className="bg-gray-50 dark:bg-gray-900 px-6 py-3 flex gap-3 border-t border-gray-100 dark:border-gray-700">
                <button
                    onClick={onVerDetalles}
                    className="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded font-medium transition"
                >
                    Ver Detalles
                </button>
                <button className="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 py-2 rounded font-medium transition" disabled>
                    Editar
                </button>
            </div>
        </div>
    );
}