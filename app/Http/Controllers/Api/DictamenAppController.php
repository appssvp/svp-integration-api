<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HIngreso;
use App\Models\SqlSvp\CrucesCop;
use Illuminate\Support\Facades\Log;
use App\Models\HDictamenApp;
use App\Models\HDictamenForzadoApp;
use App\Models\HCapturaOperacion;


class DictamenAppController extends Controller
{
    public function obtenerIngresosPorRango(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $horaInicio = $request->input('hora_inicio');
        $horaFin = $request->input('hora_fin');

        if (!$fechaInicio || !$fechaFin || !$horaInicio || !$horaFin) {
            return response()->json(['error' => 'Parámetros incompletos.'], 400);
        }

        // Formato completo para el rango de fecha y hora
        $inicio = $fechaInicio . ' ' . $horaInicio . ':00';
        $fin = $fechaFin . ' ' . $horaFin . ':59';

        $motivosPermitidos = [
            'ingreso_dado',
            'Por_Recarga',
            'Usuario_frecuente_o_saldo_valido',
            'Usuario_frecuente',
            'ingreso_por_apoyo'
        ];

        $ingresos = HIngreso::whereBetween('fecha_registro', [$inicio, $fin])
            ->whereIn('motivo', $motivosPermitidos)
            ->whereNotIn('id', function ($query) {
                $query->select('id_ingresos')
                    ->from('h_dictamen_app');
            })
            ->orderBy('fecha_registro', 'desc')
            ->get([
                'id',
                'nombre',
                'fecha_registro',
                'tag',
                'placa',
                'fechahoracrm',
                'saldocrm',
                'estatus',
                'tipo',
                'lugar',
                'motivo',
                'fechahoraccp',
                'saldoccp',
            ]);

        return response()->json($ingresos);
    }

    public function obtenerCrucesPorTag(Request $request)
    {
        try {
            $tag = $request->query('tag');
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            if (!$tag || !$fechaInicio || !$fechaFin) {
                return response()->json(['success' => false, 'message' => 'Faltan parámetros: tag, fecha_inicio y fecha_fin.'], 422);
            }

            $datos = CrucesCop::obtenerCrucesPorTagYRango($tag, $fechaInicio, $fechaFin);
            return response()->json(['success' => true, 'data' => $datos]);
        } catch (\Exception $e) {
            Log::error("Error por TAG: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno.', 'error' => $e->getMessage()], 500);
        }
    }

    public function obtenerCrucesPorPlaca(Request $request)
    {
        try {
            $placa = $request->query('placa');
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            if (!$placa || !$fechaInicio || !$fechaFin) {
                return response()->json(['success' => false, 'message' => 'Faltan parámetros: placa, fecha_inicio y fecha_fin.'], 422);
            }

            $datos = CrucesCop::obtenerCrucesPorMatriculaYRango($placa, $fechaInicio, $fechaFin);
            return response()->json(['success' => true, 'data' => $datos]);
        } catch (\Exception $e) {
            Log::error("Error por PLACA: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno.', 'error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'operador_dictamina' => 'required|string',
            'id_ingresos' => 'required|integer',
            'estatus' => 'required|string',
            'importe' => 'required|numeric',
            'num_id' => 'required|string'
        ]);

        try {
            $dictamen = HDictamenApp::create([
                'operador_dictamina' => $request->operador_dictamina,
                'fecha_dictamen' => now(),
                'id_ingresos' => $request->id_ingresos,
                'estatus' => $request->estatus,
                'importe' => intval($request->importe),
                'num_id' => $request->num_id,
            ]);

            return response()->json([
                'message' => 'Dictamen guardado correctamente',
                'data' => $dictamen
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el dictamen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerForzadosPorRango(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio'  => 'required|date_format:H:i',
            'hora_fin'     => 'required|date_format:H:i|after_or_equal:hora_inicio',
        ]);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $horaInicio = $request->input('hora_inicio');
        $horaFin = $request->input('hora_fin');

        $inicio = $fechaInicio . ' ' . $horaInicio . ':00';
        $fin = $fechaFin . ' ' . $horaFin . ':59';

        $idsDictaminados = HDictamenForzadoApp::pluck('id_forzados')->toArray();

        $registros = HCapturaOperacion::whereBetween('fecha_hora', [$inicio, $fin])
            ->where('tipo', 'forzado')
            ->whereNotIn('id', $idsDictaminados)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $registros,
        ]);
    }

    public function storeForzados(Request $request)
    {
        $request->validate([
            'operador_dictamina' => 'required|string',
            'id_ingresos' => 'required|integer',
            'estatus' => 'required|string',
            'importe' => 'required|numeric',
            'num_id' => 'required|string'
        ]);

        try {
            $dictamen = HDictamenForzadoApp::create([
                'operador_dictamina' => $request->operador_dictamina,
                'fecha_dictamen' => now(),
                'id_forzados' => $request->id_ingresos,
                'estatus' => $request->estatus,
                'importe' => intval($request->importe),
                'num_id' => $request->num_id,
            ]);

            return response()->json([
                'message' => 'Dictamen guardado correctamente',
                'data' => $dictamen
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el dictamen',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
