<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModificadorGrupo extends Model
{
    protected $table = 'modificador_grupo';
    public $timestamps = false;
    protected $guarded = ['id'];
}
