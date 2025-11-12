<?php

namespace App\Models\SqlSvp;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CrucesMatriculasFrecuenteCop
{
    public static function obtenerPorTag($tag)
    {
        // Validación del TAG
        if (!preg_match('/^[A-Z0-9]+$/i', $tag)) {
            throw new \InvalidArgumentException("Tag inválido.");
        }

        // Fechas
        $fechaFin = Carbon::now()->format('Y-m-d');
        $fechaInicio = Carbon::now()->subDays(170)->format('Y-m-d');

        // Consulta con CASE en lugar de DECODE
        $query = "
            SELECT TOP 8 * FROM OPENQUERY(COPSVP, '
                SELECT
                    MATRICULA AS MATRICULA_FRECUENTE,
                    SUM(CASE WHEN check_seg_co IN (1, 2, 3) THEN 1 ELSE 0 END) AS AUT,
                    SUM(CASE WHEN check_seg_co IS NULL THEN 1 ELSE 0 END) AS MAN,
                    COUNT(PAN) AS CRUCES_CON_TAG
                FROM cop.viajescop
                WHERE 
                    PAN = ''{$tag}''
                    AND FECHAHORAFIN BETWEEN TO_DATE(''{$fechaInicio}'', ''YYYY-MM-DD'') 
                        AND TO_DATE(''{$fechaFin}'', ''YYYY-MM-DD'')
                    AND MATRICULA IS NOT NULL
                    AND PAN NOT IN (''OHLM00009026'')
                GROUP BY MATRICULA, PAN
                ORDER BY CRUCES_CON_TAG DESC
            ')
        ";

        // Ejecutar en conexión correcta
        return DB::connection('sqlsrv_104')->select(DB::raw($query));
    }
}
