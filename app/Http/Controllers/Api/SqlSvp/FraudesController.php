<?php

namespace App\Http\Controllers\Api\SqlSvp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SqlSvp\FraudesCop;

class FraudesController extends Controller
{
public function obtenerResumenFraudesPorFecha(Request $request)
{
    try {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        if (!$fechaInicio || !$fechaFin) {
            return response()->json([
                'success' => false,
                'message' => 'Los parámetros "fecha_inicio" y "fecha_fin" son requeridos'
            ], 400);
        }

        // Convertir al formato yyyymmdd si vienen con guiones
        try {
            $fechaInicio = \Carbon\Carbon::parse($fechaInicio)->format('Ymd');
            $fechaFin = \Carbon\Carbon::parse($fechaFin)->format('Ymd');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Formato de fecha inválido. Usa yyyy-mm-dd o yyyymmdd.'
            ], 400);
        }

        // Llamar al modelo
        $datos = FraudesCop::obtenerResumenPorFechas($fechaInicio, $fechaFin);

        return response()->json([
            'success' => true,
            'data' => $datos
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
