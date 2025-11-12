<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HCapturaOperacion extends Model
{
    protected $table = 'h_captura_operacion';

    public $timestamps = false;

    protected $connection = 'svp';

    protected $fillable = [
        'fecha_hora',
        'nombre_captura',
        'nombre',
        'via',
        'tipo',
        'entidad',
        'dependencia',
        'motivo',
        'modelo',
        'clasificacion',
        'empresa',
        'nombre_proveedor',
        'placa',
        'placa2',
        'tag',
        'add_placa_app',
    ];

    public function dictamenes()
    {
        return $this->hasMany(HDictamenForzadoApp::class, 'id_forzados');
    }

    public static function obtenerResumenPorVia($fechaInicio, $fechaFin)
    {
        return self::selectRaw("
            via,
            SUM(CASE WHEN motivo = 'Tag dañado o de difícil acceso' THEN 1 ELSE 0 END) AS tag_danado,
            SUM(CASE WHEN motivo = 'Tag en placa' THEN 1 ELSE 0 END) AS tag_placa,
            SUM(CASE WHEN motivo = 'Un Tag 2 Vehículos' THEN 1 ELSE 0 END) AS tag_doble
        ")
            ->where('tipo', 'forzado')
            ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('via')
            ->orderBy('via')
            ->get();
    }

    public static function obtenerResumenPorEstatus($fechaInicio, $fechaFin)
    {
        return self::leftJoin('h_dictamen_forzados_app as d', 'h_captura_operacion.id', '=', 'd.id_forzados')
            ->selectRaw("
            SUM(CASE WHEN d.id_forzados IS NULL THEN 1 ELSE 0 END) AS faltantes,
            SUM(CASE WHEN d.estatus = 'Cobrada' THEN 1 ELSE 0 END) AS cobrados,
            SUM(CASE WHEN d.estatus = 'No cobrada' THEN 1 ELSE 0 END) AS no_cobrados
        ")
            ->where('h_captura_operacion.tipo', 'forzado')
            ->whereBetween('h_captura_operacion.fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->first();
    }

    public static function obtenerResumenPorMes($anio)
    {
        $registros = self::selectRaw("
        motivo,
        MONTH(fecha_hora) as mes,
        COUNT(*) as total
    ")
            ->where('tipo', 'forzado')
            ->whereYear('fecha_hora', $anio)
            ->groupBy('motivo', 'mes')
            ->orderBy('motivo')
            ->orderBy('mes')
            ->get();

        $resultado = [];

        foreach ($registros as $registro) {
            $motivo = $registro->motivo === '0' || $registro->motivo === 0 ? 'Sin motivo' : $registro->motivo;
            $mes = (int) $registro->mes;

            if (!isset($resultado[$motivo])) {
                $resultado[$motivo] = array_fill(1, 12, 0);
                $resultado[$motivo]['total'] = 0;
            }

            $resultado[$motivo][$mes] = $registro->total;
            $resultado[$motivo]['total'] += $registro->total;
        }


        $resultado = collect($resultado)->map(function ($valores) {
            $formato = [];
            foreach ($valores as $mes => $valor) {
                $formato[(string)$mes] = $valor;
            }
            return $formato;
        });

        return $resultado->toArray();
    }


    public static function resumenPorHora($fechaInicio, $fechaFin)
    {
        // Datos por hora
        $porHora = self::selectRaw('HOUR(fecha_hora) as hora, COUNT(*) as total')
            ->where('tipo', 'forzado')
            ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        // Total general
        $total = self::where('tipo', 'forzado')
            ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->count();

        return [
            'por_hora' => $porHora,
            'total' => $total
        ];
    }

    public static function resumenPorDia($fechaInicio, $fechaFin)
    {
        return self::selectRaw('
            DATE(fecha_hora) as dia,
            COUNT(*) as total
        ')
            ->where('tipo', 'forzado')
            ->whereBetween('fecha_hora', [
                $fechaInicio . ' 00:00:00',
                $fechaFin . ' 23:59:59'
            ])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();
    }

    public static function resumenPorAnio()
    {
        $registros = self::selectRaw("
            motivo,
            YEAR(fecha_hora) as anio,
            COUNT(*) as total
        ")
            ->where('tipo', 'forzado')
            ->groupBy('motivo', 'anio')
            ->orderBy('motivo')
            ->orderBy('anio')
            ->get();

        $resultado = [];

        foreach ($registros as $registro) {
            $motivo = $registro->motivo;
            $anio = (int) $registro->anio;

            if (!isset($resultado[$motivo])) {
                $resultado[$motivo] = [];
            }

            $resultado[$motivo][$anio] = $registro->total;
        }

        return $resultado;
    }

    public static function obtenerResumenPorTipoYMesPorAnio($anio)
{
    $mesLimite = now()->year == $anio ? now()->month : 12;

    $registros = self::selectRaw("
        tipo,
        MONTH(fecha_hora) AS mes,
        COUNT(*) AS total
    ")
    ->whereYear('fecha_hora', $anio)
    ->groupBy('tipo', 'mes')
    ->orderBy('mes')
    ->get();

    $datosAgrupados = [];

    foreach ($registros as $registro) {
        $tipo = $registro->tipo ?? 'Sin Tipo';
        $mes = intval($registro->mes);
        $datosAgrupados[$tipo][$mes] = $registro->total;
    }

    $resultados = [];
    foreach ($datosAgrupados as $tipo => $meses) {
        $fila = ['tipo' => $tipo];
        for ($i = 1; $i <= $mesLimite; $i++) {
            $fila["mes_$i"] = $meses[$i] ?? 0;
        }
        $resultados[] = $fila;
    }

    return $resultados;
}


    public static function obtenerResumenPorTipoYMes($fechaInicio, $fechaFin)
{
    $query = self::query();

    if ($fechaInicio && $fechaFin) {
        $query->whereBetween('fecha_hora', [
            $fechaInicio . ' 00:00:00',
            $fechaFin . ' 23:59:59'
        ]);
    }

    $resumen = $query->selectRaw("
            tipo,
            MONTH(fecha_hora) AS mes,
            COUNT(*) AS total
        ")
        ->groupBy('tipo', 'mes')
        ->orderBy('mes')
        ->get();

    // Agrupar por tipo y distribuir en meses 1-12
    $datosAgrupados = [];
    foreach ($resumen as $registro) {
        $tipo = $registro->tipo ?? 'Sin Tipo';
        $mes = intval($registro->mes);
        $datosAgrupados[$tipo][$mes] = $registro->total;
    }

    $resultados = [];
    foreach ($datosAgrupados as $tipo => $meses) {
        $fila = ['tipo' => $tipo];
        for ($i = 1; $i <= 12; $i++) {
            $fila["mes_$i"] = $meses[$i] ?? 0;
        }
        $resultados[] = $fila;
    }

    return $resultados;
}

public static function obtenerResumenPorHora($fechaInicio = null, $fechaFin = null)
{
    $query = self::query();

    if ($fechaInicio && $fechaFin) {
        $query->whereBetween('fecha_hora', [
            $fechaInicio . ' 00:00:00',
            $fechaFin . ' 23:59:59'
        ]);
    }

    return $query->selectRaw('HOUR(fecha_hora) as hora, COUNT(*) as total')
                 ->groupBy('hora')
                 ->orderBy('hora')
                 ->get();
}


public static function resumenPorDiaCaptura($fechaInicio, $fechaFin)
{
    // Capturas por día (nombre del día y total)
    $porDia = self::selectRaw('DAYNAME(fecha_hora) as dia, COUNT(*) as total')
        ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
        ->groupByRaw('DAYNAME(fecha_hora)')
        ->get();

    // Total general
    $total = self::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
        ->count();

    return [
        'por_dia' => $porDia,
        'total' => $total
    ];
}

public static function obtenerResumenPorMesCaptura($anio)
{
    $registros = self::selectRaw("
        tipo,
        MONTH(fecha_hora) as mes,
        COUNT(*) as total
    ")
        ->whereYear('fecha_hora', $anio)
        ->groupBy('tipo', 'mes')
        ->orderBy('tipo')
        ->orderBy('mes')
        ->get();

    $resultado = [];

    foreach ($registros as $registro) {
        $tipo = $registro->tipo ?: 'Desconocido';
        $mes = (int) $registro->mes;

        if (!isset($resultado[$tipo])) {
            $resultado[$tipo] = array_fill(1, 12, 0);
            $resultado[$tipo]['total'] = 0;
        }

        $resultado[$tipo][$mes] = $registro->total;
        $resultado[$tipo]['total'] += $registro->total;
    }

    // Convertir a formato compatible (clave de mes como string)
    $resultado = collect($resultado)->map(function ($valores) {
        $formato = [];
        foreach ($valores as $mes => $valor) {
            $formato[(string) $mes] = $valor;
        }
        return $formato;
    });

    return $resultado->toArray();
}



public static function resumenPorAnioCaptura()
{
    $registros = self::selectRaw("
        tipo,
        YEAR(fecha_hora) as anio,
        COUNT(*) as total
    ")
        ->groupBy('tipo', 'anio')
        ->orderBy('tipo')
        ->orderBy('anio')
        ->get();

    $resultado = [];

    foreach ($registros as $registro) {
        $tipo = $registro->tipo ?: 'Desconocido';
        $anio = (int) $registro->anio;

        if (!isset($resultado[$tipo])) {
            $resultado[$tipo] = [];
        }

        $resultado[$tipo][$anio] = $registro->total;
    }

    return $resultado;
}







}
