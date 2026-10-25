import { useState } from 'react';
import Buscador from '../components/Buscador';
import ResultadoCliente from '../components/ResultadoCliente';
import ClienteDetalle from '../components/ClienteDetalle';
import BarraSuperior from '@/components/BarraSuperior';
import clientesDemo from '../data/clientesDemo';

// TODO: la base de datos real todavía no está montada/importada.
// Mientras tanto buscamos en `clientesDemo` (datos de prueba en memoria)
// en vez de llamar a /api/search. Cuando haya datos reales, sustituir
// buscarEnDemo() por la llamada fetch al backend (ver docs/ para el
// estado de SearchController vs DashboardController).
function buscarEnDemo(query, tipo) {
    const q = query.toLowerCase();

    return clientesDemo.filter((cliente) => {
        switch (tipo) {
            case 'nombre':
                return cliente.nombre?.toLowerCase().includes(q);
            case 'dni':
                return cliente.identificacion?.toLowerCase().includes(q);
            case 'telefono':
                return cliente.telefono?.toLowerCase().includes(q);
            case 'email':
                return cliente.email?.toLowerCase().includes(q);
            case 'poliza':
                return (cliente.polizas || []).some((poliza) =>
                    poliza.numero_poliza?.toLowerCase().includes(q)
                );
            case 'matricula':
                return (cliente.polizas || []).some((poliza) =>
                    poliza.matricula?.toLowerCase().includes(q)
                );
            default:
                return false;
        }
    });
}

function BuscadorPage() {
    const [resultados, setResultados] = useState([]);
    const [loading, setLoading] = useState(false);
    const [busqueda, setBusqueda] = useState('');
    const [mensaje, setMensaje] = useState('');
    const [clienteSeleccionado, setClienteSeleccionado] = useState(null);

    const handleSearch = async (query, tipo) => {
        setClienteSeleccionado(null);
        setBusqueda(query);

        if (query.length < 1) {
            setResultados([]);
            setMensaje('');
            return;
        }

        setLoading(true);
        try {
            const encontrados = buscarEnDemo(query, tipo);
            setResultados(encontrados);
            setMensaje(
                encontrados.length === 0
                    ? 'No se encontraron resultados'
                    : `Se encontraron ${encontrados.length} cliente(s) (datos de prueba)`
            );
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-b from-gray-50 dark:from-gray-900 to-gray-100 dark:to-gray-950">
            <BarraSuperior />

            {/* Search Section */}
            <div className="container mx-auto px-4 py-20">
                <div className="max-w-2xl mx-auto">
                    <div className="flex justify-center mb-8">
                        <img
                            src="/images/logo-cordoba-gestion.jpeg"
                            alt="Córdoba Gestión"
                            className="h-20 w-auto dark:rounded-lg dark:bg-white dark:p-2"
                        />
                    </div>
                    <Buscador onSearch={handleSearch} loading={loading} />
                </div>

                {/* Mensaje de estado */}
                {busqueda && mensaje && (
                    <div className="max-w-2xl mx-auto mt-6">
                        <p className="text-center text-gray-600 dark:text-gray-300 font-medium">
                            {mensaje}
                        </p>
                    </div>
                )}

                {/* Resultados */}
                <div className="max-w-4xl mx-auto mt-10">
                    {clienteSeleccionado ? (
                        <ClienteDetalle
                            cliente={clienteSeleccionado}
                            onVolver={() => setClienteSeleccionado(null)}
                        />
                    ) : loading ? (
                        <div className="flex justify-center py-12">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-800"></div>
                        </div>
                    ) : resultados.length > 0 ? (
                        <div className="grid gap-6">
                            {resultados.map((cliente) => (
                                <ResultadoCliente
                                    key={cliente.id}
                                    cliente={cliente}
                                    onVerDetalles={() => setClienteSeleccionado(cliente)}
                                />
                            ))}
                        </div>
                    ) : busqueda ? (
                        <div className="text-center py-12">
                            <p className="text-gray-500 dark:text-gray-400 text-lg">
                                No hay resultados para tu búsqueda
                            </p>
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <p className="text-gray-400 dark:text-gray-500 text-lg">
                                Comienza a buscar clientes y pólizas
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default BuscadorPage;