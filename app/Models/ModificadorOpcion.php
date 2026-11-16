<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModificadorOpcion extends Model
{
    protected $table = 'modificador_opcion';
    public $timestamps = false;
    protected $guarded = ['id'];
}
