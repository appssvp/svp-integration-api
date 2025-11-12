<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ListaPersonalOperacion;
use Illuminate\Http\Request;

class ListaPersonalOperacionController extends Controller
{
    public function index(Request $request)
    {
        $personal = ListaPersonalOperacion::all();

        return response()->json($personal);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'num_empleado' => 'required|string|max:10',
            'nombre' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'fecha_ingreso' => 'required|date',
            'usuario_registro' => 'required|string|max:100',
        ]);

        $validated['fecha_registro'] = now();

        $registro = ListaPersonalOperacion::create($validated);

        return response()->json([
            'message' => 'Personal registrado correctamente',
            'data' => $registro
        ], 201);
    }
    public function destroy($id)
    {
        $registro = ListaPersonalOperacion::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $registro->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    public function update(Request $request, $id)
    {
        $registro = ListaPersonalOperacion::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $registro->update($request->only([
            'num_empleado',
            'nombre',
            'puesto',
            'fecha_ingreso'
        ]));

        return response()->json(['message' => 'Registro actualizado correctamente']);
    }
}
