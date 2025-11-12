<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HIngreso;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\HDictamenApp;
use Illuminate\Support\Facades\DB;

class HIngresoController extends Controller
{
    /**
     * Devuelve los registros de h_ingresos filtrados por fecha
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            $ingresos = HIngreso::with([
                'dictamenes:id,id_ingresos,estatus,operador_dictamina,fecha_dictamen,importe,num_id'
            ])
                ->whereBetween('fecha_registro', [$inicioCompleto, $finCompleto])
                ->orderBy('fecha_registro', 'desc')
                ->get();

            $ingresos->transform(function ($ingreso) {
                $dictamen = $ingreso->dictamenes->first();

                $ingreso->estatus_dictamen = $dictamen->estatus ?? null;
                $ingreso->operador_dictamina = $dictamen->operador_dictamina ?? null;
                $ingreso->fecha_dictamen = $dictamen->fecha_dictamen ?? null;
                $ingreso->importe = $dictamen->importe ?? null;
                $ingreso->num_id = $dictamen->num_id ?? null;

                unset($ingreso->dictamenes);

                return $ingreso;
            });

            return response()->json([
                'status' => 'success',
                'data' => $ingresos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los ingresos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resumen(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            $ingresos = HIngreso::whereBetween('fecha_registro', [$inicioCompleto, $finCompleto])
                ->selectRaw('
                lugar,
                SUM(CASE WHEN motivo = "ingreso_por_apoyo" THEN 1 ELSE 0 END) as ingresos_por_apoyo,
                SUM(CASE WHEN motivo = "Por_Recarga" THEN 1 ELSE 0 END) as recarga,
                SUM(CASE WHEN motivo = "sin_saldo" THEN 1 ELSE 0 END) as sin_saldo,
                SUM(CASE WHEN motivo = "tag_no_valido" THEN 1 ELSE 0 END) as tag_no_valido,
                SUM(CASE WHEN motivo = "Usuario_frecuente_o_saldo_valido" THEN 1 ELSE 0 END) as usuario_frecuente,
                COUNT(*) as total
            ')
                ->groupBy('lugar')
                ->orderBy('lugar')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $ingresos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el resumen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function importeRecuperado(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            // Obtener los ingresos con sus dictámenes dentro del rango
            $ingresos = HIngreso::with(['dictamenes:id,id_ingresos,estatus,importe,fecha_dictamen'])
                ->whereBetween('fecha_registro', [$inicioCompleto, $finCompleto])
                ->get();

            // Sumar solo los importes donde el dictamen tenga estatus "Cobrada"
            $totalImporte = $ingresos->reduce(function ($carry, $ingreso) {
                $dictamen = $ingreso->dictamenes->first();
                if (
                    $dictamen &&
                    $dictamen->estatus === 'Cobrada' &&
                    is_numeric($dictamen->importe)
                ) {
                    return $carry + $dictamen->importe;
                }
                return $carry;
            }, 0);

            return response()->json([
                'status' => 'success',
                'importe_recuperado' => $totalImporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al calcular el importe recuperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function resumenPorEstatus(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            $resultado = HIngreso::obtenerResumenPorEstatus($inicioCompleto, $finCompleto);

            return response()->json([
                'status' => 'success',
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener resumen por estatus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function resumenPorMes(Request $request)
{
    try {
        $fechaInicio = $request->query('fecha_inicio');
        $anio = date('Y', strtotime($fechaInicio));

        $resumen = HIngreso::obtenerResumenPorMesAnual($anio);

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


    public function resumenPorDia(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            $registros = HIngreso::whereBetween('fecha_registro', [$inicioCompleto, $finCompleto])
                ->selectRaw('DATE(fecha_registro) as dia, COUNT(*) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $registros
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener resumen por día',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resumenPorHora(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            $registros = HIngreso::whereBetween('fecha_registro', [$inicioCompleto, $finCompleto])
                ->selectRaw('HOUR(fecha_registro) as hora, COUNT(*) as total')
                ->groupBy('hora')
                ->orderBy('hora')
                ->get();

            $totalGlobal = $registros->sum('total');

            return response()->json([
                'status' => 'success',
                'data' => $registros,
                'total' => $totalGlobal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener resumen por hora',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resumenPorAnio()
    {
        try {
            $datos = HIngreso::obtenerResumenPorAnioCompleto();

            $resumen = [
                'Ingresos' => [],
                'No ingresos' => []
            ];

            foreach ($datos as $fila) {
                $anio = $fila->anio;
                $resumen['Ingresos'][$anio] = $fila->ingresos;
                $resumen['No ingresos'][$anio] = $fila->no_ingresos;
            }

            return response()->json([
                'status' => 'success',
                'data' => $resumen
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el resumen por año',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resumenTotalPorMes(Request $request)
{
    try {
        $fechaInicio = $request->query('fecha_inicio');
        $anio = date('Y', strtotime($fechaInicio));

        $resumen = HIngreso::obtenerTotalesPorMes($anio);

        return response()->json([
            'status' => 'success',
            'data' => $resumen
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al generar la gráfica de totales por mes',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
