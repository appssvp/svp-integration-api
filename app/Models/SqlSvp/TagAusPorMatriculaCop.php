<?php

namespace App\Models\SqlSvp;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagAusPorMatriculaCop
{
    /**
     * Obtener hasta 2 tags desde LISTA_TAG_MATRICULAS_AUSUR por matrícula
     * usando un rango fijo de 90 días.
     *
     * @param string $matricula
     * @return array
     */
    public static function obtenerPorMatricula($matricula)
    {
        if (!preg_match('/^[A-Z0-9]+$/i', $matricula)) {
            throw new \InvalidArgumentException("Matrícula inválida.");
        }

        $dias = 90;
        $fechaActual = Carbon::now();
        $fechaInicio = $fechaActual->copy()->subDays($dias)->format('Ymd');
        $fechaFin = $fechaActual->format('Ymd');

        $query = "
            DECLARE @matricula VARCHAR(40)
            SET @matricula = '{$matricula}'
            EXEC(
                'SELECT TOP 2 * FROM OPENQUERY(COPSVP, ''
                    SELECT 
                        n_tag, 
                        COUNT(*) AS Veces, 
                        MAX(fecha) AS fechaact
                    FROM COP.LISTA_TAG_MATRICULAS_AUSUR
                    WHERE matricula = '''''' + @matricula + ''''''
                    AND fecha BETWEEN ''{$fechaInicio}'' AND ''{$fechaFin}''
                    GROUP BY n_tag, matricula
                    ORDER BY fechaact DESC
                '')'
            )
        ";

        return DB::connection('sqlsrv_104')->select(DB::raw($query));
    }
}
