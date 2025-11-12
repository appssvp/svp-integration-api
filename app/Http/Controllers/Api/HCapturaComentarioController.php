<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HCapturaComentario;


class HCapturaComentarioController extends Controller
{
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaComentario::query();

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora_registro', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            $comentarios = $query->orderByDesc('fecha_hora_registro')->get();

            return response()->json([
                'status' => 'success',
                'data' => $comentarios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los comentarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function resumenPorComentario(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaComentario::query();

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora_registro', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            // Agrupar por tipo de comentario y contar
            $resumen = $query->select('comentarios as tipo')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('comentarios')
                ->orderByDesc('total')
                ->get();

            // Calcular el total global
            $totalGlobal = $resumen->sum('total');

            return response()->json([
                'status' => 'success',
                'data' => $resumen,
                'total_global' => $totalGlobal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el resumen por comentario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function resumenPorLugar(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaComentario::selectRaw('lugar, COUNT(*) as total')
                ->groupBy('lugar')
                ->orderByDesc('total');

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora_registro', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            $resultados = $query->get();

            return response()->json([
                'status' => 'success',
                'data' => $resultados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar resumen por lugar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function resumenPorMes(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $query = HCapturaComentario::selectRaw("
            comentarios,
            MONTH(fecha_hora_registro) as mes,
            COUNT(*) as total
        ")
                ->groupBy('comentarios', 'mes');

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora_registro', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            }

            $registros = $query->get();

            // Agrupar y acumular por tipo
            $resumen = [];
            foreach ($registros as $registro) {
                $tipo = $registro->comentarios;
                $mes = (int)$registro->mes;
                $total = (int)$registro->total;

                if (!isset($resumen[$tipo])) {
                    $resumen[$tipo] = array_fill(1, 12, 0);
                }

                $resumen[$tipo][$mes] = $total;
            }

            // Ordenar por total anual descendente
            uksort($resumen, function ($a, $b) use ($resumen) {
                $sumaA = array_sum($resumen[$a]);
                $sumaB = array_sum($resumen[$b]);
                return $sumaB <=> $sumaA; // orden descendente
            });

            return response()->json([
                'status' => 'success',
                'data' => $resumen
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar resumen por mes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
