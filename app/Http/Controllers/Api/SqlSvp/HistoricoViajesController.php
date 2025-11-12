<?php

namespace App\Http\Controllers\Api\SqlSvp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SqlSvp\CrucesCop;
use App\Models\SqlSvp\CrucesFrecuentesCop;
use App\Models\SqlSvp\CrucesMatriculasFrecuenteCop;
use App\Models\SqlSvp\TagPorMatriculaCop;
use App\Models\SqlSvp\TagAusPorMatriculaCop;
use App\Models\SqlSvp\SaldosCop;
use Illuminate\Support\Facades\Log;

class HistoricoViajesController extends Controller

{
    public function obtenerSaldoPorTag(Request $request)
    {
        try {
            $tag = $request->query('tag');

            if (!$tag) {
                return response()->json(['error' => 'El parámetro "tag" es requerido'], 400);
            }

            $datos = SaldosCop::obtenerSaldoPorTag($tag);

            return response()->json([
                'success' => true,
                'data' => $datos
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al obtener saldo por tag: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
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
            Log::error("Error al obtener cruces: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno.', 'error' => $e->getMessage()], 500);
        }
    }

    public function obtenerResumenFrecuentePorTag(Request $request)
    {
        try {
            $tag = $request->query('tag');

            if (!$tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'El parámetro "tag" es requerido'
                ], 400);
            }

            $datos = CrucesFrecuentesCop::obtenerResumenFrecuente($tag);

            return response()->json([
                'success' => true,
                'data' => $datos
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerMatriculasFrecuentesPorTag(Request $request)
    {
        try {
            $tag = $request->query('tag');

            if (!$tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'El parámetro "tag" es requerido.'
                ], 400);
            }

            $datos = CrucesMatriculasFrecuenteCop::obtenerPorTag($tag);

            return response()->json([
                'success' => true,
                'data' => $datos
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function obtenerTagAusPorMatricula(Request $request)
{
    try {
        $matricula = $request->query('matricula');

        if (!$matricula) {
            return response()->json([
                'success' => false,
                'message' => 'El parámetro "matricula" es requerido.'
            ], 422);
        }

        $datos = TagAusPorMatriculaCop::obtenerPorMatricula($matricula);

        return response()->json([
            'success' => true,
            'data' => $datos
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener tags por matrícula.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function obtenerTagPorMatricula(Request $request)
{
    try {
        $matricula = $request->query('matricula');
        $modo = $request->query('modo'); // opcional: '365' o por default '170'
        $fecha = $request->query('fecha'); // opcional

        if (!$matricula) {
            return response()->json([
                'success' => false,
                'message' => 'El parámetro "matricula" es requerido.'
            ], 422);
        }

        $datos = TagPorMatriculaCop::obtenerPorMatricula($matricula, $fecha, $modo);

        return response()->json([
            'success' => true,
            'data' => $datos
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener información del tag por matrícula.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
