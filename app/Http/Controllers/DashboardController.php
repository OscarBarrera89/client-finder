<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        $clientes = Cliente::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($subQuery) use ($query) {
                    $subQuery->where('nombre', 'like', "%{$query}%")
                        ->orWhere('identificacion', 'like', "%{$query}%")
                        ->orWhereHas('polizas', function ($polizaQuery) use ($query) {
                            $polizaQuery->where('matricula', 'like', "%{$query}%")
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
