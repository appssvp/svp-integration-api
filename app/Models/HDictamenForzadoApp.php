<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HDictamenForzadoApp extends Model
{
    protected $table = 'h_dictamen_forzados_app';

    public $timestamps = false;

    protected $connection = 'svp';

    protected $fillable = [
        'id',
        'operador_dictamina',
        'fecha_dictamen',
        'id_forzados',
        'estatus',
        'importe',
        'num_id',
    ];

    public function forzados()
    {
        return $this->belongsTo(HCapturaOperacion::class, 'id_ingresos');
    }

    public static function obtenerImporteRecuperado($fechaInicio, $fechaFin)
    {
        return self::join('h_captura_operacion as o', 'h_dictamen_forzados_app.id_forzados', '=', 'o.id')
            ->whereBetween('o.fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->sum('h_dictamen_forzados_app.importe');
    }
}
