<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HCapturaSaldoProveedor extends Model
{
    protected $connection = 'svp'; 
    protected $table = 'h_captura_saldo_proveedores';
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'ultima_actualizacion',
        'proveedor',
        'ano',
        'mes',
        'dia',
        'hora',
        'minuto',
        'segundo',
        'estatus_atraso'
    ];
}
