<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HCapturaOperacion;
use App\Models\HDictamenForzadoApp;
use Carbon\Carbon;

class HCapturaForzadoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            $forzados = HCapturaOperacion::with('dictamenes')
                ->where('tipo', 'forzado')
                ->whereBetween('fecha_hora', [$inicioCompleto, $finCompleto])
                ->orderBy('fecha_hora', 'desc')
                ->get()
                ->map(function ($item) {
                    $dictamen = $item->dictamenes->first();

                    return [
                        'id'                  => $item->id,
                        'fecha_hora'          => $item->fecha_hora,
                        'nombre'              => $item->nombre,
                        'via'                 => $item->via,
                        'tipo'                => $item->tipo,
                        'motivo'              => $item->motivo,
                        'modelo'              => $item->modelo,
                        'clasificacion'       => $item->clasificacion,
                        'placa'               => $item->placa,
                        'placa2'              => $item->placa2,
                        'estatus'             => $dictamen->estatus ?? null,
                        'operador_dictamina'  => $dictamen->operador_dictamina ?? null,
                        'fecha_dictamen'      => $dictamen->fecha_dictamen ?? null,
                        'importe'             => $dictamen->importe ?? null,
                        'num_id'              => $dictamen->num_id ?? null,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data'   => $forzados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al obtener los registros forzados',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function resumenPorVia(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $resumen = HCapturaOperacion::obtenerResumenPorVia($fechaInicio, $fechaFin);

            return response()->json([
                'status' => 'success',
                'data'   => $resumen
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al obtener el resumen por vía',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function resumenPorEstatus(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', now()->format('Y-m-d'));
            $fechaFin = $request->query('fecha_fin', now()->format('Y-m-d'));

            $resumen = HCapturaOperacion::obtenerResumenPorEstatus($fechaInicio, $fechaFin);

            return response()->json([
                'status' => 'success',
                'data' => $resumen
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener resumen por estatus',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function importeRecuperado(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $importe = HDictamenForzadoApp::obtenerImporteRecuperado($fechaInicio, $fechaFin);

        return response()->json([
            'status' => 'success',
            'data' => [
                'importe_recuperado' => number_format($importe, 2)
            ]
        ]);
    }

    public function resumenPorMes(Request $request)
    {
        $anio = $request->input('anio', date('Y'));

        $resumen = HCapturaOperacion::obtenerResumenPorMes($anio);

        return response()->json([
            'status' => 'success',
            'data' => $resumen
        ]);
    }

    public function resumenPorHora(Request $request)
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

            $resumen = HCapturaOperacion::resumenPorHora($fechaInicio, $fechaFin);

            return response()->json([
                'status' => 'success',
                'data' => $resumen['por_hora'],
                'total' => $resumen['total']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar el resumen por hora',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resumenPorDia(Request $request)
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

            $resumen = HCapturaOperacion::resumenPorDia($fechaInicio, $fechaFin);
            $total = $resumen->sum('total');

            return response()->json([
                'status' => 'success',
                'data' => $resumen,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar el resumen por día',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function resumenPorAnio()
    {
        try {
            $resumen = HCapturaOperacion::resumenPorAnio();

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
