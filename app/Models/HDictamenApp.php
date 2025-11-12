<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HDictamenApp extends Model
{
    protected $connection = 'svp'; 
    protected $table = 'h_dictamen_app';
    public $timestamps = false;

    protected $fillable = [
        'operador_dictamina',
        'fecha_dictamen',
        'id_ingresos',
        'estatus',
        'importe',
        'num_id'
    ];

    // Relación: Un dictamen pertenece a un ingreso
    public function ingreso()
    {
        return $this->belongsTo(HIngreso::class, 'id_ingresos');
    }
}
