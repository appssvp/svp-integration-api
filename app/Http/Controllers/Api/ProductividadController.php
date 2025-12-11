<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HCapturaComentario;
use App\Models\HCapturaEvasion;
use App\Models\HCapturaOperacion;
use App\Models\HCapturaProveedor;
use App\Models\HIngreso;
use App\Models\ListaPersonalOperacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductividadController extends Controller
{
    public function resumenPorNombre(Request $request)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicio = $fechaInicio . ' 00:00:00';
            $fin = $fechaFin . ' 23:59:59';

            // Consultas individuales
            $comentarios = HCapturaComentario::select('nombre', DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha_hora_registro', [$inicio, $fin])
                ->whereNotNull('comentarios')
                ->where('comentarios', '!=', '')
                ->groupBy('nombre')
                ->orderByDesc('total')
                ->get();

            $evasiones = HCapturaEvasion::select('operador as nombre', DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha_hora_registro', [$inicio, $fin])
                ->groupBy('operador')
                ->orderByDesc('total')
                ->get();

            $operaciones = HCapturaOperacion::select('nombre', DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha_hora', [$inicio, $fin])
                ->where('tipo', '!=', 'forzado')
                ->groupBy('nombre')
                ->orderByDesc('total')
                ->get();

            $forzados = HCapturaOperacion::select('nombre', DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha_hora', [$inicio, $fin])
                ->where('tipo', 'forzado')
                ->groupBy('nombre')
                ->orderByDesc('total')
                ->get();

            $proveedores = HCapturaProveedor::select('operador as nombre', DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha', [$inicio, $fin])
                ->groupBy('operador')
                ->orderByDesc('total')
                ->get();

            $ingresos = HIngreso::select('nombre', DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha_registro', [$inicio, $fin])
                ->whereIn('motivo', [
                    'Por_Recarga',
                    'Usuario_frecuente_o_saldo_valido',
                    'ingreso_por_apoyo',
                    'ingreso_dado'
                ])
                ->groupBy('nombre')
                ->orderByDesc('total')
                ->get();

            $rechazos = HIngreso::select('nombre', DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha_registro', [$inicio, $fin])
                ->whereIn('motivo', [
                    'sin_saldo',
                    'tag_no_valido'
                ])
                ->groupBy('nombre')
                ->orderByDesc('total')
                ->get();

            // Obtener todos los puestos
            $puestos = ListaPersonalOperacion::select('nombre', 'puesto')->get();

            // Fusionar datos por operador
            $resumen = [];

            $insertarDatos = function ($coleccion, $clave) use (&$resumen) {
                foreach ($coleccion as $item) {
                    $nombre = $item->nombre;
                    if (!isset($resumen[$nombre])) {
                        $resumen[$nombre] = [
                            'operador' => $nombre,
                            'comentarios' => 0,
                            'evasiones' => 0,
                            'operaciones' => 0,
                            'forzados' => 0,
                            'proveedores' => 0,
                            'ingresos' => 0,
                            'rechazos' => 0,
                            'puesto' => null
                        ];
                    }
                    $resumen[$nombre][$clave] = $item->total;
                }
            };

            $insertarDatos($comentarios, 'comentarios');
            $insertarDatos($evasiones, 'evasiones');
            $insertarDatos($operaciones, 'operaciones');
            $insertarDatos($forzados, 'forzados');
            $insertarDatos($proveedores, 'proveedores');
            $insertarDatos($ingresos, 'ingresos');
            $insertarDatos($rechazos, 'rechazos');


            foreach ($puestos as $p) {
                if (isset($resumen[$p->nombre])) {
                    $resumen[$p->nombre]['puesto'] = $p->puesto;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => array_values($resumen)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el resumen por nombre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function resumenPorNombreEspecifico(Request $request)
{
    try {

        $fechaInicio = $request->query('fecha_inicio', now()->format('Y-m-d'));
        $fechaFin = $request->query('fecha_fin', now()->format('Y-m-d'));
        $nombre = $request->query('nombre');

        if (!$nombre) {
            return response()->json(['success' => false, 'message' => 'El nombre es obligatorio'], 400);
        }

        $inicio = $fechaInicio . ' 00:00:00';
        $fin = $fechaFin . ' 23:59:59';

        $comentarios = HCapturaComentario::where('nombre', $nombre)
            ->whereBetween('fecha_hora_registro', [$inicio, $fin])
            ->whereNotNull('comentarios')
            ->where('comentarios', '!=', '')
            ->count();

        $evasiones = HCapturaEvasion::where('operador', $nombre) 
            ->whereBetween('fecha_hora_registro', [$inicio, $fin])
            ->count();

        $operaciones = HCapturaOperacion::where('nombre', $nombre)
            ->whereBetween('fecha_hora', [$inicio, $fin])
            ->where('tipo', '!=', 'forzado')
            ->count();

        $forzados = HCapturaOperacion::where('nombre', $nombre)
            ->whereBetween('fecha_hora', [$inicio, $fin])
            ->where('tipo', 'forzado')
            ->count();

        $proveedores = HCapturaProveedor::where('operador', $nombre)
            ->whereBetween('fecha', [$inicio, $fin])
            ->count();

        $ingresos = HIngreso::where('nombre', $nombre)
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->whereIn('motivo', ['Por_Recarga', 'Usuario_frecuente_o_saldo_valido', 'ingreso_por_apoyo', 'ingreso_dado'])
            ->count();

        $rechazos = HIngreso::where('nombre', $nombre)
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->whereIn('motivo', ['sin_saldo', 'tag_no_valido'])
            ->count();

        $empleado = ListaPersonalOperacion::where('nombre', $nombre)->first();
        $puesto = $empleado ? $empleado->puesto : null;

        $datos = [
            'operador'    => $nombre,
            'comentarios' => $comentarios,
            'evasiones'   => $evasiones,
            'operaciones' => $operaciones,
            'forzados'    => $forzados,
            'proveedores' => $proveedores,
            'ingresos'    => $ingresos,
            'rechazos'    => $rechazos,
            'puesto'      => $puesto
        ];

        return response()->json([
            'success' => true,
            'data'    => $datos
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error en el servidor al obtener operador',
            'error'   => $e->getMessage()
        ], 500);
    }
}
}
