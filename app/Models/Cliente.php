<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_clientes';

    protected $fillable = [
        'nombre',
        'identificacion',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'cliente_id');
    }
}
