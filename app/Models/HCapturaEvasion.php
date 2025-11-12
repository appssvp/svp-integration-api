<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HCapturaEvasion extends Model
{
    protected $connection = 'svp'; 
    protected $table = 'h_captura_evasiones';
    public $timestamps = false;

    protected $fillable = [
        'fecha_hora_registro',
        'fh_registro_operador',
        'operador',
        'via',
        'enlace',
        'marca',
        'modelo',
        'color',
        'tipo',
        'placa',
        'tag_numero',
        'tag_estado',
        'barrerazo_tipo',
        'clasificacion_barrerazo',
        'v_ingresa_via',
        'clasificacion',
        'danos_barrera',
        'danos_vehiculo',
        'u_reclama_danos',
        'cobrado_no_cobrado',
        'r_img_entrada',
        'r_img_salida',
        'id_img_entrada',
        'id_img_salida',
        'enlace_img_entrada',
        'enlace_img_salida',
        'enlace_video',
        'observaciones'
    ];
}
