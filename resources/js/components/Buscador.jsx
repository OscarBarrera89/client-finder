import { useState } from 'react';
import { Search } from 'lucide-react';

export const TIPOS_BUSQUEDA = [
    { id: 'nombre', label: 'Nombre', placeholder: 'Busca por nombre del cliente...' },
    { id: 'dni', label: 'DNI/NIE/CIF', placeholder: 'Busca por DNI, NIE o CIF...' },
    { id: 'telefono', label: 'Teléfono', placeholder: 'Busca por teléfono...' },
    { id: 'email', label: 'Email', placeholder: 'Busca por email...' },
    { id: 'poliza', label: 'Póliza', placeholder: 'Busca por número de póliza...' },
    { id: 'matricula', label: 'Matrícula', placeholder: 'Busca por matrícula...' },
];

export default function Buscador({ onSearch }) {
    const [query, setQuery] = useState('');
    const [tipo, setTipo] = useState(TIPOS_BUSQUEDA[0].id);

    const tipoActual = TIPOS_BUSQUEDA.find((t) => t.id === tipo) ?? TIPOS_BUSQUEDA[0];

    const handleChange = (e) => {
        const value = e.target.value;
        setQuery(value);
        onSearch(value, tipo);
    };

    const handleTipoChange = (nuevoTipo) => {
        setTipo(nuevoTipo);
        onSearch(query, nuevoTipo);
    };

    const handleClear = () => {
        setQuery('');
        onSearch('', tipo);
    };

    return (
        <div className="relative">
            {/* Selector de tipo de búsqueda */}
            <div className="mb-3 flex flex-wrap justify-center gap-2">
                {TIPOS_BUSQUEDA.map((t) => (
                    <button
                        key={t.id}
                        type="button"
                        onClick={() => handleTipoChange(t.id)}
                        className={`px-3 py-1.5 rounded-full text-sm font-medium transition border ${
                            tipo === t.id
                                ? 'bg-red-600 text-white border-red-600'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-700'
                        }`}
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            <div className="relative flex items-center">
                <Search className="absolute left-4 text-gray-400 dark:text-gray-500" size={24} />
                <input
                    type="text"
                    value={query}
                    onChange={handleChange}
                    placeholder={tipoActual.placeholder}
                    className="w-full pl-12 pr-12 py-4 text-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-2 border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:border-blue-800 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition"
                />
                {query && (
                    <button
                        onClick={handleClear}
                        className="absolute right-4 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition"
                        aria-label="Limpiar búsqueda"
                    >
                        ✕
                    </button>
                )}
            </div>
        </div>
    );
}