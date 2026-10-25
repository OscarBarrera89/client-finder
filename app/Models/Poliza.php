<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poliza extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_polizas';

    protected $fillable = [
        'cliente_id',
        'compania_id',
        'ramo_id',
        'estado_id',
        'numero_poliza',
        'prima_anual',
        'fecha_contratacion',
        'fecha_vencimiento',
        'fecha_anulacion',
        'tipo_vencimiento_id',
        'matricula',
        'marca_modelo',
        'activo',
        'p_produccion',
        'p_cartera',
        'riesgo',
        'observaciones',
    ];

    protected $casts = [
        'prima_anual' => 'decimal:2',
        'p_produccion' => 'decimal:2',
        'p_cartera' => 'decimal:2',
        'fecha_contratacion' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_anulacion' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Una póliza pertenece a un cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Una póliza pertenece a una compañía.
     */
    public function compania()
    {
        return $this->belongsTo(Compania::class, 'compania_id');
    }

    /**
     * Una póliza pertenece a un ramo.
     */
    public function ramo()
    {
        return $this->belongsTo(Ramo::class, 'ramo_id');
    }

    /**
     * La póliza pertenece a un tipo de vencimiento.
     */
    public function tipoVencimiento()
    {
        return $this->belongsTo(TipoVencimiento::class, 'tipo_vencimiento_id');
    }

    /**
     * La póliza pertenece a un estado.
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
