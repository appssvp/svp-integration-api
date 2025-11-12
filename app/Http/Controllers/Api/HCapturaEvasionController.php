<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HCapturaEvasion;
use Illuminate\Http\Request;

class HCapturaEvasionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaEvasion::query();

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora_registro', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            $evasiones = $query->orderByDesc('fecha_hora_registro')->get();

            return response()->json([
                'status' => 'success',
                'data' => $evasiones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las evasiones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function resumenMensualSimple(Request $request)
{
    $fechaInicio = $request->input('fecha_inicio');
    $fechaFin = $request->input('fecha_fin');

    $query = HCapturaEvasion::query();

    if ($fechaInicio && $fechaFin) {
        $query->whereBetween('fecha_hora_registro', [
            $fechaInicio . ' 00:00:00',
            $fechaFin . ' 23:59:59'
        ]);
    } elseif ($fechaInicio) {
        $query->where('fecha_hora_registro', '>=', $fechaInicio . ' 00:00:00');
    } elseif ($fechaFin) {
        $query->where('fecha_hora_registro', '<=', $fechaFin . ' 23:59:59');
    }

    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo',
        4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre',
        10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];

    $resultados = array_fill_keys(array_values($meses), 0);

    $query->get()->each(function ($registro) use (&$resultados, $meses) {
        $mes = intval(date('n', strtotime($registro->fecha_hora_registro)));
        $nombreMes = $meses[$mes];
        $resultados[$nombreMes]++;
    });

    $resultados['total'] = array_sum($resultados);

    return response()->json($resultados);
}

}
