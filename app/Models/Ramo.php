<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ramo extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_ramos';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Un ramo puede tener muchas pólizas.
     */
    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'ramo_id');
    }
}
