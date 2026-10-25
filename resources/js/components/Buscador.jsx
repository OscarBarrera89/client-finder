import { Search } from 'lucide-react';
import { useState } from 'react';

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
                        className={`rounded-full border px-3 py-1.5 text-sm font-medium transition ${
                            tipo === t.id
                                ? 'border-red-600 bg-red-600 text-white'
                                : 'border-gray-200 bg-white text-gray-600 hover:border-red-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-red-700'
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
                    className="w-full rounded-lg border-2 border-gray-200 bg-white py-4 pr-12 pl-12 text-lg text-gray-900 transition focus:border-blue-800 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-900"
                />
                {query && (
                    <button
                        onClick={handleClear}
                        className="absolute right-4 text-gray-400 transition hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                        aria-label="Limpiar búsqueda"
                    >
                        ✕
                    </button>
                )}
            </div>
        </div>
    );
}
