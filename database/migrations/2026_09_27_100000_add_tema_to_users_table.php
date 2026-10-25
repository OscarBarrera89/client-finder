<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tema visual elegido por cada usuario (se guarda en la cuenta, no en el navegador).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // claro, oscuro o sistema (sigue el tema del dispositivo)
            $table->string('tema')->default('sistema')->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tema');
        });
    }
};
