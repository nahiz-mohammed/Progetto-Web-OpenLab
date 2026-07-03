<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifica', function (Blueprint $table) {
            $table->integer('ID_Utente_Mittente')->nullable()->after('ID_Utente_Destinatario');
            $table->foreign('ID_Utente_Mittente')
                  ->references('ID_Utente')
                  ->on('utente')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifica', function (Blueprint $table) {
            $table->dropForeign(['ID_Utente_Mittente']);
            $table->dropColumn('ID_Utente_Mittente');
        });
    }
};
