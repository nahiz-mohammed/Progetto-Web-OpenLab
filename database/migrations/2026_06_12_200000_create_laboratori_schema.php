<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // UTENTE
        Schema::create('utente', function (Blueprint $table) {
            $table->integer('ID_Utente')->autoIncrement();
            $table->string('Username', 100)->unique();
            $table->string('Password', 255);
            $table->enum('Ruolo', ['Admin', 'Professore', 'Studente']);
            $table->tinyInteger('Richiede_Cambio_Password')->default(0);
        });

        // AULA
        Schema::create('aula', function (Blueprint $table) {
            $table->integer('ID_Aula')->autoIncrement();
            $table->string('Nome_Aula', 100);
            $table->string('Tipologia_Aula', 100);
            $table->integer('Capienza')->default(0);
            $table->enum('Stato', ['Disponibile', 'Manutenzione'])->default('Disponibile');
        });

        // CORSO
        Schema::create('corso', function (Blueprint $table) {
            $table->integer('ID_Corso')->autoIncrement();
            $table->string('Nome', 150);
            $table->string('Tipologia_Materia', 100);
            $table->integer('ID_Professore');
            
            $table->foreign('ID_Professore')->references('ID_Utente')->on('utente')->onUpdate('cascade')->onDelete('restrict');
        });

        // PRENOTAZIONE
        Schema::create('prenotazione', function (Blueprint $table) {
            $table->integer('ID_Prenotazione')->autoIncrement();
            $table->date('Data');
            $table->time('Ora_Inizio');
            $table->time('Ora_Fine');
            $table->integer('ID_Corso');
            $table->integer('ID_Aula');
            
            $table->foreign('ID_Corso')->references('ID_Corso')->on('corso')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('ID_Aula')->references('ID_Aula')->on('aula')->onUpdate('cascade')->onDelete('restrict');
        });

        // ISCRIZIONE_CORSO
        Schema::create('iscrizione_corso', function (Blueprint $table) {
            $table->integer('ID_Iscrizione')->autoIncrement();
            $table->integer('ID_Studente');
            $table->integer('ID_Corso');
            $table->enum('Stato', ['In attesa', 'Approvato', 'Rifiutato'])->default('In attesa');
            $table->date('Data_Richiesta')->useCurrent();
            
            $table->unique(['ID_Studente', 'ID_Corso']);
            $table->foreign('ID_Studente')->references('ID_Utente')->on('utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('ID_Corso')->references('ID_Corso')->on('corso')->onUpdate('cascade')->onDelete('cascade');
        });

        // CONFERMA_PRESENZA
        Schema::create('conferma_presenza', function (Blueprint $table) {
            $table->integer('ID_Conferma')->autoIncrement();
            $table->integer('ID_Studente');
            $table->integer('ID_Prenotazione');
            $table->tinyInteger('Confermata')->default(0);
            $table->dateTime('Timestamp_Conferma')->nullable();
            
            $table->unique(['ID_Studente', 'ID_Prenotazione']);
            $table->foreign('ID_Studente')->references('ID_Utente')->on('utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('ID_Prenotazione')->references('ID_Prenotazione')->on('prenotazione')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferma_presenza');
        Schema::dropIfExists('iscrizione_corso');
        Schema::dropIfExists('prenotazione');
        Schema::dropIfExists('corso');
        Schema::dropIfExists('aula');
        Schema::dropIfExists('utente');
    }
};
