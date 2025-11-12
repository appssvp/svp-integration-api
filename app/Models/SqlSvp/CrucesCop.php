<?php

namespace App\Models\SqlSvp;

use Illuminate\Support\Facades\DB;


class CrucesCop
{
    public static function obtenerCrucesPorTagYRango($tag, $fechaInicio, $fechaFin)
    {
        if (!preg_match('/^[A-Z0-9]+$/i', $tag)) {
            throw new \InvalidArgumentException("Tag inválido.");
        }

        if (!preg_match('/^\d{8}$/', $fechaInicio) || !preg_match('/^\d{8}$/', $fechaFin)) {
            throw new \InvalidArgumentException("Las fechas deben tener formato yyyymmdd.");
        }

        // Aquí armamos las fechas con el formato completo
        $fechaInicioOracle = $fechaInicio . ' 00:00';
        $fechaFinOracle = $fechaFin . ' 23:59';

        $query = "
    SELECT TOP 100 * FROM OPENQUERY(COPSVP, '
        SELECT 
            TO_CHAR(FECHAARMADO, ''YYYY-MM-DD HH24:MI:SS'') AS FECHAARMADO,
            PAN,
            MATRICULA,
            TO_CHAR(FECHAHORAINI, ''YYYY-MM-DD HH24:MI:SS'') AS FECHAHORAINI,
            lu.descripcion AS Entrada,
            TO_CHAR(FECHAHORAFIN, ''YYYY-MM-DD HH24:MI:SS'') AS FECHAHORAFIN,
            lu2.descripcion AS Salida,
            DECODE(check_seg_co, 1, ''AUT 1'', 2, ''AUT 2'', 3, ''OCR'', NULL, ''MANUAL'') AS Armado,
            TO_CHAR(IMPORTE, ''99999990.00'') AS IMPORTE,
            num_corr_viaje
        FROM cop.viajescop vi
        JOIN cop.lugares lu ON vi.porticoini = lu.id_lugar
        JOIN cop.lugares lu2 ON vi.porticofin = lu2.id_lugar
        WHERE FECHAHORAFIN BETWEEN TO_DATE(''$fechaInicioOracle'', ''YYYYMMDD HH24:MI'') 
                              AND TO_DATE(''$fechaFinOracle'', ''YYYYMMDD HH24:MI'')
          AND PAN = ''$tag''
        ORDER BY FECHAHORAFIN DESC
    ')
";

        return DB::connection('sqlsrv_104')->select(DB::raw($query));
    }

        public static function obtenerCrucesPorMatriculaYRango($matricula, $fechaInicio, $fechaFin)
    {
        if (!preg_match('/^[A-Z0-9]+$/i', $matricula)) {
            throw new \InvalidArgumentException("Tag inválido.");
        }

        if (!preg_match('/^\d{8}$/', $fechaInicio) || !preg_match('/^\d{8}$/', $fechaFin)) {
            throw new \InvalidArgumentException("Las fechas deben tener formato yyyymmdd.");
        }

        // Aquí armamos las fechas con el formato completo
        $fechaInicioOracle = $fechaInicio . ' 00:00';
        $fechaFinOracle = $fechaFin . ' 23:59';

        $query = "
    SELECT TOP 100 * FROM OPENQUERY(COPSVP, '
        SELECT 
            TO_CHAR(FECHAARMADO, ''YYYY-MM-DD HH24:MI:SS'') AS FECHAARMADO,
            PAN,
            MATRICULA,
            TO_CHAR(FECHAHORAINI, ''YYYY-MM-DD HH24:MI:SS'') AS FECHAHORAINI,
            lu.descripcion AS Entrada,
            TO_CHAR(FECHAHORAFIN, ''YYYY-MM-DD HH24:MI:SS'') AS FECHAHORAFIN,
            lu2.descripcion AS Salida,
            DECODE(check_seg_co, 1, ''AUT 1'', 2, ''AUT 2'', 3, ''OCR'', NULL, ''MANUAL'') AS Armado,
            TO_CHAR(IMPORTE, ''99999990.00'') AS IMPORTE,
            num_corr_viaje
        FROM cop.viajescop vi
        JOIN cop.lugares lu ON vi.porticoini = lu.id_lugar
        JOIN cop.lugares lu2 ON vi.porticofin = lu2.id_lugar
        WHERE FECHAHORAFIN BETWEEN TO_DATE(''$fechaInicioOracle'', ''YYYYMMDD HH24:MI'') 
                              AND TO_DATE(''$fechaFinOracle'', ''YYYYMMDD HH24:MI'')
          AND MATRICULA = ''$matricula''
        ORDER BY FECHAHORAFIN DESC
    ')
";

        return DB::connection('sqlsrv_104')->select(DB::raw($query));
    }
}


