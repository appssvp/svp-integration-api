<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ListProveedor;

class ProveedorController extends Controller
{

    public function index()
    {
        $data = ListProveedor::select(
            'id',
            'usuario_registro',
            'fecha_registro',
            'placa',
            'nombre_proveedor',
            'empresa',
            'motivo_ingreso',
            'modelo_vehiculo',
            'clasificacion_vehiculo',
            'tag',
            'cruces_permitidos',
            'turno_permitido'
        )->orderBy('id', 'desc')->get();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_registro' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'placa' => 'required|string|max:255',            
            'nombre_proveedor' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'motivo_ingreso' => 'required|string|max:255',
            'modelo_vehiculo' => 'required|string|max:255',
            'clasificacion_vehiculo' => 'required|string|max:255',
            'tag' => 'nullable|string|max:255',
            'cruces_permitidos' => 'required|integer|min:0',
            'turno_permitido' => 'required|string|max:255',

        ]);

        $registro = ListProveedor::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Proveedor guardado correctamente.',
            'data' => $registro
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $proveedor = ListProveedor::findOrFail($id);
        $field = array_keys($request->all())[0]; 
        $value = $request->input($field);
        $proveedor->$field = $value;
        $proveedor->save();

        return response()->json(['message' => 'Actualizado']);
    }

    public function destroy($id)
{
    $proveedor = ListProveedor::find($id);

    if (!$proveedor) {
        return response()->json(['message' => 'Registro no encontrado'], 404);
    }

    $proveedor->delete();

    return response()->json(['message' => 'Proveedor eliminado correctamente']);
}

}
