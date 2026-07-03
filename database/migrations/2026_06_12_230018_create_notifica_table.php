<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifica', function (Blueprint $table) {
            $table->id('ID_Notifica');
            $table->integer('ID_Utente_Destinatario');
            $table->string('Titolo', 255);
            $table->text('Messaggio');
            $table->string('Tipo', 50)->default('info');
            $table->boolean('Letta')->default(false);
            $table->timestamp('Data_Invio')->useCurrent();

            // Foreign key relation matching the signed integer type in utente
            $table->foreign('ID_Utente_Destinatario')
                  ->references('ID_Utente')
                  ->on('utente')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifica');
    }
};
