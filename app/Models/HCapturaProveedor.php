<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HCapturaProveedor extends Model
{
    protected $table = 'h_captura_proveedores';

    protected $fillable = [
        'fecha',
        'operador',
        'via',
        'motivo',
        'modelo',
        'clasificacion',
        'empresa',
        'proveedor',
        'placa',
        'cruces_registrados',
        'turno_permitido',
        'comentarios',
        'cruces_faltantes'
    ];


    public function getFechaAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
