<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListProveedor extends Model
{
    protected $table = 'list_proveedores';

    protected $fillable = [
        'usuario_registro',
        'fecha_registro',
        'placa',        
        'nombre_proveedor',
        'empresa',
        'motivo_ingreso',
        'modelo_vehiculo',
        'clasificacion_vehiculo',
        'tag',
        'cruces_permitidos',
        'turno_permitido'
        
    ];
}
