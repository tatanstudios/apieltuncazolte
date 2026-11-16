<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesDescripcionOpcion extends Model
{
    protected $table = 'ordenes_descripcion_opcion';
    public $timestamps = false;
    protected $guarded = ['id'];
}
