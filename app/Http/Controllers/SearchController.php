<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Poliza;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Búsqueda de clientes o pólizas para el buscador público (BuscadorPage).
     * type=cliente busca por nombre/email/teléfono/identificación.
     * type=poliza busca por número de póliza o matrícula.
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $type = $request->input('type', 'cliente'); // cliente, poliza

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Por favor ingresa al menos 2 caracteres',
            ]);
        }

        try {
            if ($type === 'poliza') {
                $resultados = Poliza::where('numero_poliza', 'like', "%{$query}%")
                    ->orWhere('matricula', 'like', "%{$query}%")
                    ->with('cliente', 'compania', 'ramo', 'estado')
                    ->limit(20)
                    ->get();
            } else {
                $resultados = Cliente::where('nombre', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('telefono', 'like', "%{$query}%")
                    ->orWhere('identificacion', 'like', "%{$query}%")
                    ->with('polizas.compania', 'polizas.ramo', 'polizas.estado')
                    ->limit(20)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $resultados,
                'count' => $resultados->count(),
            ]);
        } catch (\Exception $e) {
            // El detalle queda en el log del servidor; al navegador solo va un mensaje genérico
            report($e);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error en la búsqueda',
            ], 500);
        }
    }
}
