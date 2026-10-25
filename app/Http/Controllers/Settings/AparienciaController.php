<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tema claro/oscuro de cada usuario. Se guarda en su cuenta para que
 * se aplique en cualquier dispositivo desde el que entre.
 */
class AparienciaController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('settings/appearance');
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tema' => ['required', Rule::in(User::TEMAS)],
        ]);

        $request->user()->update($datos);

        return back();
    }
}
