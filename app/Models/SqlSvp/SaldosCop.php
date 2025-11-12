<?php


namespace App\Models\SqlSvp;

use Illuminate\Support\Facades\DB;

class SaldosCop
{
public static function obtenerSaldoPorTag($tag)
{
    if (!preg_match('/^[A-Z0-9]+$/i', $tag)) {
        throw new \InvalidArgumentException("Tag inválido");
    }

    $query = "
        SELECT * FROM OPENQUERY(COPSVP, '
            SELECT 
                TAGID, 
                CAST(saldo AS DECIMAL(16,2)) AS SALDO, 
                CASE 
                    WHEN tipotag = 1 THEN ''PRE-PAGO''
                    WHEN tipotag = 2 THEN ''POST-PAGO''
                    ELSE ''DESCONOCIDO''
                END AS Estatus
            FROM cop.tags
            WHERE tagidsia = ''" . $tag . "'' 
        ')
    ";

    return DB::connection('sqlsrv_104')->select(DB::raw($query));
}

}


