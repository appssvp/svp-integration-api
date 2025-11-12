<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaPersonalOperacion extends Model
{
    protected $table = 'lista_personal_operacion';

    protected $fillable = [
        'num_empleado',
        'nombre',
        'puesto',
        'usuario_registro',
        'fecha_ingreso'
    ];

    public $timestamps = false;
}
