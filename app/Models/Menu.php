<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'menu';

    public $timestamps = false;

    protected $fillable = ['nombre', 'ruta', 'icono', 'grupo', 'orden', 'padre_id', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'padre_id');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(Menu::class, 'padre_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_menu', 'menu_id', 'rol_id');
    }
}
