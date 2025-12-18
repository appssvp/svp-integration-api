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

    private function obtenerIngresosConDictamenes($nombre, $inicio, $fin, $motivos)
    {
        return HIngreso::with([
            'dictamenes:id,id_ingresos,estatus,operador_dictamina,fecha_dictamen,importe,num_id'
        ])
            ->where('nombre', $nombre)
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->whereIn('motivo', $motivos)
            ->orderByDesc('fecha_registro')
            ->get()
            ->transform(function ($ingreso) {
                $dictamen = $ingreso->dictamenes->first();

                $ingreso->estatus_dictamen = $dictamen->estatus ?? null;
                $ingreso->operador_dictamina = $dictamen->operador_dictamina ?? null;
                $ingreso->fecha_dictamen = $dictamen->fecha_dictamen ?? null;
                $ingreso->importe = $dictamen->importe ?? null;
                $ingreso->num_id = $dictamen->num_id ?? null;

                unset($ingreso->dictamenes);
                return $ingreso;
            });
    }

    private function obtenerForzadosConDictamenes($nombre, $inicio, $fin)
    {
        return HCapturaOperacion::with([
            'dictamenes:id,id_forzados,estatus,operador_dictamina,fecha_dictamen,importe,num_id'
        ])
            ->where('nombre', $nombre)
            ->where('tipo', 'forzado')
            ->whereBetween('fecha_hora', [$inicio, $fin])
            ->orderByDesc('fecha_hora')
            ->get()
            ->transform(function ($forzado) {
                $dictamen = $forzado->dictamenes->first();

                $forzado->estatus_dictamen = $dictamen->estatus ?? null;
                $forzado->operador_dictamina = $dictamen->operador_dictamina ?? null;
                $forzado->fecha_dictamen = $dictamen->fecha_dictamen ?? null;
                $forzado->importe = $dictamen->importe ?? null;
                $forzado->num_id = $dictamen->num_id ?? null;

                unset($forzado->dictamenes);
                return $forzado;
            });
    }
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

            $exentos = HCapturaOperacion::select('nombre', DB::raw('COUNT(*) as total'))
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
                            'exentos' => 0,
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
            $insertarDatos($exentos, 'exentos');
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

            $exentos = HCapturaOperacion::where('nombre', $nombre)
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
                'exentos' => $exentos,
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


    // Y actualizar el método detalleAppMovil:
    public function detalleAppMovil(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'tipo'   => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date',
        ]);

        $nombre = $request->nombre;
        $inicio = $request->fecha_inicio . ' 00:00:00';
        $fin    = $request->fecha_fin . ' 23:59:59';

        switch ($request->tipo) {
            case 'comentarios':
                return HCapturaComentario::where('nombre', $nombre)
                    ->whereBetween('fecha_hora_registro', [$inicio, $fin])
                    ->whereNotNull('comentarios')
                    ->where('comentarios', '!=', '')
                    ->orderByDesc('fecha_hora_registro')
                    ->get();

            case 'evasiones':
                return HCapturaEvasion::where('operador', $nombre)
                    ->whereBetween('fecha_hora_registro', [$inicio, $fin])
                    ->orderByDesc('fecha_hora_registro')
                    ->get();

            case 'exentos':
                return HCapturaOperacion::where('nombre', $nombre)
                    ->where('tipo', 'exento')  
                    ->whereBetween('fecha_hora', [$inicio, $fin])
                    ->orderByDesc('fecha_hora')
                    ->get();

            case 'forzados':
                return $this->obtenerForzadosConDictamenes($nombre, $inicio, $fin);

            case 'ingresos':
                return $this->obtenerIngresosConDictamenes($nombre, $inicio, $fin, [
                    'Por_Recarga',
                    'Usuario_frecuente_o_saldo_valido',
                    'ingreso_por_apoyo',
                    'ingreso_dado'
                ]);

            case 'rechazos':
                return $this->obtenerIngresosConDictamenes($nombre, $inicio, $fin, [
                    'sin_saldo',
                    'tag_no_valido'
                ]);

            default:
                return response()->json([], 200);
        }
    }
}
