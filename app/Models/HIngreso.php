<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HIngreso extends Model
{
    protected $connection = 'svp';
    protected $table = 'h_ingresos';
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'fechahoracrm',
        'tag',
        'placa',
        'saldocrm',
        'estatus',
        'lugar',
        'motivo',
        'nombre',
        'fechahoraccp',
        'saldoccp',
        'tipo',
    ];

    // Relación: Un ingreso puede tener varios dictámenes
    public function dictamenes()
    {
        return $this->hasMany(HDictamenApp::class, 'id_ingresos');
    }

    public static function obtenerResumenPorEstatus($inicioCompleto, $finCompleto)
    {
        return self::leftJoin('h_dictamen_app as d', 'h_ingresos.id', '=', 'd.id_ingresos')
            ->whereBetween('h_ingresos.fecha_registro', [$inicioCompleto, $finCompleto])
            ->selectRaw("
            SUM(CASE 
                WHEN h_ingresos.motivo IN ('sin_saldo', 'tag_no_valido') THEN 1
                ELSE 0
            END) AS rechazos,

            SUM(CASE 
                WHEN d.id IS NULL AND h_ingresos.motivo NOT IN ('sin_saldo', 'tag_no_valido') THEN 1
                ELSE 0
            END) AS faltantes_dictaminar,

            SUM(CASE 
                WHEN d.estatus = 'Cobrada' AND h_ingresos.motivo NOT IN ('sin_saldo', 'tag_no_valido') THEN 1
                ELSE 0
            END) AS cobrados,

            SUM(CASE 
                WHEN d.estatus = 'No cobrada' AND h_ingresos.motivo NOT IN ('sin_saldo', 'tag_no_valido') THEN 1
                ELSE 0
            END) AS no_cobrados,

            COUNT(*) AS total
        ")
            ->first();
    }


    public static function obtenerResumenPorMesAnual($anio)
    {
        // Rango de enero a diciembre del año solicitado
        $inicioCompleto = "$anio-01-01 00:00:00";
        $finCompleto = "$anio-12-31 23:59:59";

        // Obtener todos los motivos existentes en ese año
        $motivos = self::select('motivo')
            ->whereNotNull('motivo')
            ->where('motivo', '!=', '')
            ->whereYear('fecha_registro', $anio)
            ->groupBy('motivo')
            ->pluck('motivo')
            ->toArray();

        // Inicializar estructura
        $resumen = [];
        foreach ($motivos as $motivo) {
            $resumen[$motivo] = array_fill(1, 12, 0);
        }

        // Obtener los totales por mes
        $registros = self::selectRaw("
            motivo,
            MONTH(fecha_registro) as mes,
            COUNT(*) as total
        ")
            ->whereNotNull('motivo')
            ->where('motivo', '!=', '')
            ->whereBetween('fecha_registro', [$inicioCompleto, $finCompleto])
            ->whereYear('fecha_registro', $anio)
            ->groupBy('motivo', 'mes')
            ->get();

        foreach ($registros as $registro) {
            $motivo = $registro->motivo;
            $mes = (int) $registro->mes;
            $total = (int) $registro->total;

            if (!isset($resumen[$motivo])) {
                $resumen[$motivo] = array_fill(1, 12, 0);
            }

            $resumen[$motivo][$mes] = $total;
        }

        // Ordenar por total descendente
        uksort($resumen, function ($a, $b) use ($resumen) {
            return array_sum($resumen[$b]) <=> array_sum($resumen[$a]);
        });

        return $resumen;
    }



    public static function obtenerResumenPorAnioCompleto()
    {
        return self::selectRaw("
                YEAR(fecha_registro) as anio,
                SUM(CASE 
                    WHEN motivo IN ('Por_Recarga', 'ingreso_por_apoyo', 'ingreso_dado', 'Usuario_frecuente_o_saldo_valido') THEN 1 
                    ELSE 0 
                END) AS ingresos,
                SUM(CASE 
                    WHEN motivo IN ('sin_saldo', 'tag_no_valido') THEN 1 
                    ELSE 0 
                END) AS no_ingresos
            ")
            ->groupBy('anio')
            ->orderBy('anio')
            ->get();
    }



    public static function obtenerTotalesPorMes($anio)
    {
        $motivosIngresos = [
            'Por_Recarga',
            'ingreso_por_apoyo',
            'Usuario_frecuente_o_saldo_valido',
            'ingreso_dado'
        ];

        $motivosNoIngresos = [
            'sin_saldo',
            'tag_no_valido'
        ];

        $registros = self::selectRaw("
            motivo,
            MONTH(fecha_registro) as mes,
            COUNT(*) as total
        ")
            ->whereYear('fecha_registro', $anio)
            ->whereNotNull('motivo')
            ->where('motivo', '!=', '')
            ->groupBy('motivo', 'mes')
            ->get();

        $resumen = [
            'Ingresos' => array_fill(1, 12, 0),
            'No ingresos' => array_fill(1, 12, 0)
        ];

        foreach ($registros as $registro) {
            $mes = (int)$registro->mes;
            $motivo = $registro->motivo;
            $total = (int)$registro->total;

            if (in_array($motivo, $motivosIngresos)) {
                $resumen['Ingresos'][$mes] += $total;
            } elseif (in_array($motivo, $motivosNoIngresos)) {
                $resumen['No ingresos'][$mes] += $total;
            }
        }

        // Calcular total anual
        $totalAnual = 0;
        for ($mes = 1; $mes <= 12; $mes++) {
            $totalAnual += $resumen['Ingresos'][$mes] + $resumen['No ingresos'][$mes];
        }

        $resumen['Total anual'] = $totalAnual;

        return $resumen;
    }
}
