<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HCapturaOperacion;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HCapturaOperacionController extends Controller
{
    /**
     * Retorna los registros filtrados por fecha
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaOperacion::query();

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            $operaciones = $query->orderByDesc('fecha_hora')->get();

            return response()->json([
                'status' => 'success',
                'data' => $operaciones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las operaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resumenPorLugar(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaOperacion::query();

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            $resumen = $query->selectRaw('
                via AS lugar,
                SUM(CASE WHEN tipo = "exento" THEN 1 ELSE 0 END) AS exentos,
                SUM(CASE WHEN tipo = "proveedor" THEN 1 ELSE 0 END) AS proveedor,
                SUM(CASE WHEN tipo = "forzado" THEN 1 ELSE 0 END) AS forzados,
                SUM(CASE WHEN tipo = "ecobus_rtp" THEN 1 ELSE 0 END) AS rtp,
                COUNT(*) AS total
            ')
                ->groupBy('via')
                ->orderBy('via')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $resumen
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar el resumen por lugar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function resumenPorMes(Request $request)
{
    try {
        $fechaInicio = $request->query('fecha_inicio');

        if (!$fechaInicio) {
            return response()->json([
                'status' => 'error',
                'message' => 'La fecha de inicio es requerida'
            ], 400);
        }

        $anio = \Carbon\Carbon::parse($fechaInicio)->year;
        $resultados = HCapturaOperacion::obtenerResumenPorTipoYMesPorAnio($anio);

        return response()->json([
            'status' => 'success',
            'data' => $resultados
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al generar el resumen por mes',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function resumenPorHora(Request $request)
{
    try {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        $resumen = HCapturaOperacion::obtenerResumenPorHora($fechaInicio, $fechaFin);

        // Asegura que siempre regresen las 24 horas (0 a 23)
        $horas = array_fill(0, 24, 0);
        foreach ($resumen as $item) {
            $horas[intval($item->hora)] = intval($item->total);
        }

        return response()->json([
            'status' => 'success',
            'data' => $horas
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener resumen por hora',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function resumenPorDiaCaptura(Request $request)
{
    try {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        if (!$fechaInicio || !$fechaFin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fechas requeridas'
            ], 400);
        }

        $resumen = HCapturaOperacion::resumenPorDiaCaptura($fechaInicio, $fechaFin);

        return response()->json([
            'status' => 'success',
            'data' => $resumen['por_dia'],
            'total' => $resumen['total']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al generar el resumen por día',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function resumenPorMesCaptura(Request $request)
{
    $anio = $request->input('anio', date('Y'));

    $resumen = HCapturaOperacion::obtenerResumenPorMesCaptura($anio);

    return response()->json([
        'status' => 'success',
        'data' => $resumen
    ]);
}

public function resumenPorAnioCaptura()
{
    try {
        $resumen = HCapturaOperacion::resumenPorAnioCaptura();

        return response()->json([
            'status' => 'success',
            'data' => $resumen
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al generar resumen por año',
            'error' => $e->getMessage()
        ], 500);
    }
}



}
