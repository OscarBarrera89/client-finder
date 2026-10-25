export const claseInput =
    'w-full rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 px-3 py-2 focus:border-blue-800 dark:focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-800 dark:focus:ring-blue-400';

export const claseBoton = 'rounded-md bg-blue-800 px-4 py-2 font-medium text-white hover:bg-blue-900 disabled:opacity-60';

/**
 * Campo de formulario con etiqueta y mensaje de error.
 */
export default function Campo({ id, label, error, ...props }) {
    return (
        <div>
            <label htmlFor={id} className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {label}
            </label>
            <input id={id} className={claseInput} {...props} />
            {error && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{error}</p>}
        </div>
    );
}
