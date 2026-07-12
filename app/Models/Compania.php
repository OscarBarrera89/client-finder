<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compania extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_companias';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Una compañía puede tener muchas pólizas.
     */
    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'compania_id');
    }
}