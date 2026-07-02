<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitaPagina extends Model
{
    protected $table = 'visita_pagina';

    const CREATED_AT = null;

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = ['pagina', 'contador'];
}
