<?php

namespace App\Models\SqlSvp;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagPorMatriculaCop
{
    /**
     * Obtener información del TAG asociado a una matrícula con resumen de cruces
     * usando un rango fijo de 90 días.
     */
    public static function obtenerPorMatricula($matricula)
    {
        if (!preg_match('/^[A-Z0-9]+$/i', $matricula)) {
            throw new \InvalidArgumentException("Matrícula inválida.");
        }

        $dias = 90;
        $referencia = Carbon::now();

        $fechaFin = $referencia->format('Ymd');
        $fechaInicio = $referencia->copy()->subDays($dias)->format('Ymd');

        $query = "
            SELECT TOP 3 * FROM OPENQUERY(COPSVP, '
                SELECT
                    PAN AS TAG,
                    DECODE(d.tipotag, 1, ''PREPAGO'', 2, ''POSTPAGO'') AS Estatus,
                    CAST(d.saldo AS DECIMAL(16,2)) AS SALDO,
                    COUNT(MATRICULA) AS TOTAL_CRUCES,
                    SUM(CASE WHEN check_seg_co IN (''1'',''2'',''3'') THEN 1 ELSE 0 END) AS AUTOMATICO,
                    SUM(CASE WHEN check_seg_co IS NULL THEN 1 ELSE 0 END) AS MANUAL
                FROM cop.VIAJESCOP a
                INNER JOIN cop.tags d ON a.tagid = d.tagid
                WHERE 
                    MATRICULA = ''{$matricula}''
                    AND d.saldo >= 0.00
                    AND PAN IS NOT NULL
                    AND FECHAHORAFIN BETWEEN TO_DATE(''{$fechaInicio} 00:00:00'', ''YYYYMMDD HH24:MI:SS'') 
                        AND TO_DATE(''{$fechaFin} 23:59:59'', ''YYYYMMDD HH24:MI:SS'')
                    AND MATRICULA IS NOT NULL
                GROUP BY pan, tipotag, saldo, matricula
                ORDER BY TOTAL_CRUCES DESC
            ')
        ";

        return DB::connection('sqlsrv_104')->select(DB::raw($query));
    }
}
