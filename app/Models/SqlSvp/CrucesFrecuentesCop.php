<?php

namespace App\Models\SqlSvp;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CrucesFrecuentesCop
{
    public static function obtenerResumenFrecuente($tag)
    {
        // Validar el TAG
        if (!preg_match('/^[A-Z0-9]+$/i', $tag)) {
            throw new \InvalidArgumentException("Tag inválido.");
        }

        // Fechas
        $fechaFin = Carbon::now()->format('Ymd'); // hoy
        $fechaInicio = Carbon::now()->subDays(140)->format('Ymd'); // hace 140 días

        $query = "
            SELECT TOP 8 * FROM OPENQUERY(COPSVP, '
                SELECT 
                    b.descripcion AS ENTRADA_FRECUENTE,
                    c.descripcion AS SALIDA_FRECUENTE,
                    SUM(CASE WHEN check_seg_co IN (1, 2, 3) THEN 1 ELSE 0 END) AS AUTOMATICO,
                    SUM(CASE WHEN check_seg_co IS NULL THEN 1 ELSE 0 END) AS MANUAL,
                    COUNT(*) AS CRUCES
                FROM cop.viajescop a
                INNER JOIN cop.lugares b ON a.porticoini = b.id_lugar
                INNER JOIN cop.lugares c ON a.porticofin = c.id_lugar
                WHERE 
                    PAN = ''{$tag}'' 
                    AND FECHAHORAFIN BETWEEN TO_DATE(''{$fechaInicio}'',''YYYYMMDD'') 
                                        AND TO_DATE(''{$fechaFin}'',''YYYYMMDD'') 
                    AND PAN NOT IN (''OHLM00009026'')
                GROUP BY b.descripcion, c.descripcion
                ORDER BY CRUCES DESC
            ')
        ";

        return DB::connection('sqlsrv_104')->select(DB::raw($query));
    }
}
