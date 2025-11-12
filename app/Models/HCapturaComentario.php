<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HCapturaComentario extends Model
{
    protected $connection = 'svp';
    protected $table = 'h_captura_comentarios';
    public $timestamps = false;

    protected $fillable = [
        'fecha_hora_registro',
        'tag',
        'placa',
        'lugar',
        'nombre',
        'comentarios',
        'detalle_otros'
    ];
}
