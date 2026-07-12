<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoVencimiento extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_tipos_vencimiento';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Un tipo de vencimiento puede estar asociado a muchas pólizas.
     */
    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'tipo_vencimiento_id');
    }
}