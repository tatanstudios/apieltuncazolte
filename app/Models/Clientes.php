<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Clientes extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'clientes';
    public $timestamps = false;

    protected $hidden = ['password', 'codigo_recuperacion'];
}
