<?php

namespace App\Http\Controllers;

use App\Importacion\Normalizador;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        // DNI y matrícula se guardan normalizados (sin espacios ni guiones): se busca también así
        $identificacion = Normalizador::identificacion($query);
        $matricula = Normalizador::matricula($query);

        $clientes = Cliente::query()
            ->when($query !== '', function ($builder) use ($query, $identificacion, $matricula) {
                $builder->where(function ($subQuery) use ($query, $identificacion, $matricula) {
                    $subQuery->where('nombre', 'like', "%{$query}%")
                        ->orWhere('identificacion', 'like', "%{$query}%")
                        ->when($identificacion !== '', fn ($q) => $q->orWhere('identificacion', 'like', "%{$identificacion}%"))
                        ->orWhereHas('polizas', function ($polizaQuery) use ($query, $matricula) {
                            $polizaQuery->where('matricula', 'like', "%{$query}%")
                                ->when($matricula !== '', fn ($q) => $q->orWhere('matricula', 'like', "%{$matricula}%"))
                                ->orWhere('numero_poliza', 'like', "%{$query}%")
                                ->orWhereHas('compania', function ($companiaQuery) use ($query) {
                                    $companiaQuery->where('nombre', 'like', "%{$query}%");
                                });
                        });
                });
            })
            ->with('polizas.compania')
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('dashboard', [
            'clientes' => $clientes,
            'query' => $query,
        ]);
    }
}
