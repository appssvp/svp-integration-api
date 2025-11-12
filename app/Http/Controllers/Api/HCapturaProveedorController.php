<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HCapturaProveedor;
use Carbon\Carbon;

class HCapturaProveedorController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Fechas por query o valores por defecto
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->format('Y-m-d'));
            $fechaFin    = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));

            $inicioCompleto = $fechaInicio . ' 00:00:00';
            $finCompleto    = $fechaFin . ' 23:59:59';

            $registros = HCapturaProveedor::whereBetween('fecha', [$inicioCompleto, $finCompleto])
                ->orderByDesc('fecha')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $registros
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los registros de proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
