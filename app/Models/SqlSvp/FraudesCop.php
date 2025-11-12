<?php

namespace App\Models\SqlSvp;

use Illuminate\Support\Facades\DB;
use App\Models\HIngreso;
use Carbon\Carbon;

class FraudesCop
{
    public static function obtenerResumenPorFechas($fechaInicio, $fechaFin)
    {
        try {
            $fechaInicio = Carbon::parse($fechaInicio)->format('Ymd');
            $fechaFin = Carbon::parse($fechaFin)->format('Ymd');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Fechas inválidas.");
        }

        $tagPlacas = HIngreso::select('tag', 'placa')->get()->map(function ($item) {
            return [
                'TAG' => strtoupper(trim($item->tag)),
                'PLACA' => strtoupper(trim(str_replace('-', '', $item->placa))),
            ];
        })->toArray();

        $placasImagen = ['EXCLUIDA1', 'EXCLUIDA2', 'ETC']; 

        $query = "
            DECLARE @fechaini VARCHAR(16) = '{$fechaInicio}';
            DECLARE @fechafin VARCHAR(16) = '{$fechaFin}';

            EXEC(
            '
            SELECT 
                a.fecha_hora AS FECHA_HORA,
                a.id_lugar AS ENLACE,
                a.id_lugar AS ID_LUGAR,
                a.descripcion AS VIA,
                a.n_transito AS N_TRANSITO,
                a.n_tarjeta AS N_TARJETA,
                a.mpago AS MPAGO,
                a.mpaso AS MPASO,
                a.cr AS CR,
                a.veces AS VECES,
                a.hora_transaccion_tag AS HORA_TRANSACCION_TAG,
                a.CONTRACT_AUTHENTICATOR AS SALDO,
                a.duplicado AS DUPLICADO,
                b.fechahorapc AS FECHAHORAPC,
                b.ident_pc AS IDENT_PC,
                b.num_corr_punto AS NUM_CORR_PUNTO,
                b.pan_1 AS PAN_1,
                b.matricula AS MATRICULA,
                b.img_delantera_1 AS IMG_DELANTERA_1,
                b.num_corr_viaje AS NUM_CORR_VIAJE
            FROM (
                SELECT * FROM OPENQUERY(ccpsvp, ''
                    SELECT 
                        a.fecha_hora, a.id_lugar, b.descripcion, a.n_transito, a.n_tarjeta,
                        a.modo_pago, 
                        DECODE(a.modo_pago, 7, ''''Exento'''', 9, ''''Prepago'''', 10, ''''Postpago'''') AS mpago,
                        DECODE(a.modo_paso, 1, ''''OK'''', 4, ''''Simulado'''', 5, ''''Fraude'''', 3, ''''Forzado'''', 10, ''''Desvio/Rechazo'''', 7, ''''Reservado'''') AS mpaso,
                        a.causarechazo,
                        DECODE(a.causarechazo, 0, ''''Sin TAG'''', 1, ''''Tag OK'''', 3, ''''L. Negra'''', 5, ''''Sin Saldo'''', 7, ''''No Válido'''') AS cr,
                        a.veces, a.hora_transaccion_tag, a.CONTRACT_AUTHENTICATOR, a.duplicado 
                    FROM ccp.ttransitos a
                    INNER JOIN ccp.lugares b ON a.id_lugar = b.id_lugar
                    WHERE a.fecha_hora BETWEEN TO_DATE(''''' + @fechaini + ' 00:00:00'''', ''''yyyymmdd hh24:mi:ss'''') 
                                        AND TO_DATE(''''' + @fechafin + ' 23:59:59'''', ''''yyyymmdd hh24:mi:ss'''')
                        AND a.modo_paso = 5
                        AND a.modo_pago NOT IN (7)
                        AND a.id_lugar NOT IN (67176725)
                '')
            ) a
            LEFT JOIN (
                SELECT * FROM OPENQUERY(COPSVP, ''
                    SELECT 
                        a.fechahorapc, a.ident_pc, a.num_corr_punto,
                        a.pan_1, a.matricula, a.img_delantera_1, a.num_corr_viaje
                    FROM cop.transaccionescop a
                    WHERE a.tipo_punto_cobro = 1
                    AND a.fechahorapc BETWEEN TO_DATE(''''' + @fechaini + ' 00:00:00'''', ''''yyyymmdd hh24:mi:ss'''') 
                                          AND TO_DATE(''''' + @fechafin + ' 23:59:59'''', ''''yyyymmdd hh24:mi:ss'''')
                '')
            ) b
            ON a.n_transito = b.num_corr_punto
            AND a.id_lugar = b.ident_pc
            AND a.fecha_hora = b.fechahorapc
            ORDER BY a.fecha_hora
            ')
        ";

        $resultados = DB::connection('sqlsrv_104')->select(DB::raw($query));

        // Limpieza y enriquecimiento
        return collect($resultados)->map(function ($row) use ($tagPlacas, $placasImagen) {
            $matriculaSistema = strtoupper(trim(str_replace('-', '', $row->MATRICULA ?? '')));
            $tag = '';
            $placa = '';
            $app_tag = '';
            $app_placa = '';

            if (in_array($matriculaSistema, $placasImagen) || strpos($matriculaSistema, '001A') !== false) {
                return null;
            }

            foreach ($tagPlacas as $ref) {
                if ($row->PAN_1 === $ref['TAG']) {
                    $app_tag = $ref['TAG'];
                    $tag = 'Apoyo app tag';
                }
                if ($matriculaSistema === $ref['PLACA']) {
                    $app_placa = $ref['PLACA'];
                    $placa = 'Apoyo app placa';
                }
                if ($tag && $placa) break;
            }

            return [
                'FECHA_HORA' => Carbon::parse($row->FECHA_HORA)->format('Y-m-d H:i:s'),
                'ENLACE' => $row->ENLACE,
                'ID_LUGAR' => $row->ID_LUGAR,
                'VIA' => $row->VIA,
                'N_TRANSITO' => $row->N_TRANSITO,
                'N_TARJETA' => $row->N_TARJETA,
                'MPAGO' => $row->MPAGO,
                'MPASO' => $row->MPASO,
                'CR' => $row->CR,
                'VECES' => $row->VECES,
                'HORA_TRANSACCION_TAG' => $row->HORA_TRANSACCION_TAG,
                'SALDO' => $row->SALDO,
                'DUPLICADO' => $row->DUPLICADO,
                'FECHAHORAPC' => Carbon::parse($row->FECHAHORAPC)->format('Y-m-d H:i:s'),
                'IDENT_PC' => $row->IDENT_PC,
                'NUM_CORR_PUNTO' => $row->NUM_CORR_PUNTO,
                'PAN_1' => $row->PAN_1,
                'MATRICULA' => $row->MATRICULA,
                'IMG_DELANTERA_1' => $row->IMG_DELANTERA_1,
                'NUM_CORR_VIAJE' => $row->NUM_CORR_VIAJE,
                'app_tag' => $app_tag,
                'app_placa' => $app_placa,
                'tag' => $tag,
                'placa' => $placa,
            ];
        })->filter()->values(); 
    }
}
