<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HCapturaSaldoProveedor;
use Illuminate\Support\Carbon;

class HCapturaSaldoProveedorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaSaldoProveedor::query();

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_registro', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            $resultados = $query->orderByDesc('fecha_registro')->get();

            return response()->json([
                'status' => 'success',
                'data' => $resultados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los registros de saldo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function resumenProveedor(Request $request, $proveedor)
{
    try {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        $query = \App\Models\HCapturaSaldoProveedor::query();

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_registro', [
                $fechaInicio . ' 00:00:00',
                $fechaFin . ' 23:59:59'
            ]);
        }

        // Filtra por nombre de proveedor (sin distinción de mayúsculas/minúsculas)
        $query->whereRaw('LOWER(proveedor) = ?', [strtolower($proveedor)]);

        // Obtener todos los atrasos del proveedor
        $registros = $query->get();

        // Calcular el total de registros
        $total = $registros->count();

        // Sumar todo el tiempo en segundos
        $segundosTotales = $registros->reduce(function ($carry, $item) {
            return $carry + ($item->hora * 3600) + ($item->minuto * 60);
        }, 0);

        // Convertir segundos totales a formato HH:MM
        $horas = floor($segundosTotales / 3600);
        $minutos = floor(($segundosTotales % 3600) / 60);
        $tiempoTotal = sprintf('%02d:%02d', $horas, $minutos);

        return response()->json([
            'total_atrasos' => $total,
            'total_tiempo' => $tiempoTotal
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al calcular el resumen',
            'details' => $e->getMessage()
        ], 500);
    }
}


}
