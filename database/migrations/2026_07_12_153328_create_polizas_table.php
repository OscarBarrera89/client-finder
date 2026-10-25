<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_polizas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_poliza', 50)->unique();

            $table->foreignId('cliente_id')
                ->constrained('tbl_clientes')
                ->cascadeOnDelete();
            
            $table->foreignId('compania_id')
                ->constrained('tbl_companias')
                ->restrictOnDelete();

            $table->foreignId('ramo_id')
                ->constrained('tbl_ramos')
                ->restrictOnDelete();

            $table->foreignId('tipo_vencimiento_id')
                ->nullable()
                ->constrained('tbl_tipos_vencimiento')
                ->nullOnDelete();
            
            $table->foreignId('estado_id')
                ->nullable()
                ->constrained('tbl_estados')
                ->nullOnDelete();

            $table->string('matricula', 15)
                ->nullable()
                ->index();
            $table->string('marca_modelo', 150)->nullable();
            $table->decimal('prima_anual', 10, 2)->nullable();
            $table->decimal('p_produccion', 10, 2)->nullable();
            $table->decimal('p_cartera', 10, 2)->nullable();
            $table->date('fecha_contratacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_anulacion')->nullable();
            $table->text('riesgo')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_polizas');
    }
};