<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Aula;
use App\Models\Notifica;
use Illuminate\Support\Facades\Hash;

class OpenLabTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test del login.
     */
    public function test_login()
    {
        $password = 'password123';
        $user = User::create([
            'Username' => 'test_user',
            'Password' => Hash::make($password),
            'Ruolo' => 'Studente',
            'Richiede_Cambio_Password' => 0
        ]);

        $response = $this->postJson('/login', [
            'Username' => 'test_user',
            'Password' => $password
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'role' => 'Studente',
                     'requires_password_change' => false
                 ]);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test di forzatura cambio password.
     */
    public function test_cambio_password()
    {
        $user = User::create([
            'Username' => 'reset_user',
            'Password' => Hash::make('password123'),
            'Ruolo' => 'Studente',
            'Richiede_Cambio_Password' => 1
        ]);

        $this->actingAs($user);

        // Tentativo di accedere a una rotta protetta
        $response = $this->get('/notifiche');

        // Il middleware deve reindirizzare alla rotta del cambio password
        $response->assertRedirect(route('change.password'));
    }

    /**
     * Test della creazione di un'aula da parte dell'amministratore.
     */
    public function test_admin_creazione_aula()
    {
        $admin = User::create([
            'Username' => 'admin_user',
            'Password' => Hash::make('password123'),
            'Ruolo' => 'Admin',
            'Richiede_Cambio_Password' => 0
        ]);

        $this->actingAs($admin);

        $response = $this->postJson('/admin/aule', [
            'Nome_Aula' => 'Aula Test Alpha',
            'Tipologia_Aula' => 'Teoria',
            'Capienza' => 30,
            'Stato' => 'Disponibile'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);

        $this->assertDatabaseHas('aula', [
            'Nome_Aula' => 'Aula Test Alpha',
            'Capienza' => 30
        ]);
    }

    /**
     * Test del toggle dello stato di un'aula.
     */
    public function test_admin_toggle_aula()
    {
        $admin = User::create([
            'Username' => 'admin_user',
            'Password' => Hash::make('password123'),
            'Ruolo' => 'Admin',
            'Richiede_Cambio_Password' => 0
        ]);

        $aula = Aula::create([
            'Nome_Aula' => 'Aula Delta',
            'Tipologia_Aula' => 'Informatica',
            'Capienza' => 20,
            'Stato' => 'Disponibile'
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/admin/aule/{$aula->ID_Aula}/toggle-stato");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'stato' => 'Manutenzione'
                 ]);

        $this->assertDatabaseHas('aula', [
            'ID_Aula' => $aula->ID_Aula,
            'Stato' => 'Manutenzione'
        ]);
    }

    /**
     * Test dell'invio messaggi con corretto tracciamento del mittente.
     */
    public function test_invio_messaggi()
    {
        $prof = User::create([
            'Username' => 'prof_sender',
            'Password' => Hash::make('password123'),
            'Ruolo' => 'Professore',
            'Richiede_Cambio_Password' => 0
        ]);

        $student = User::create([
            'Username' => 'student_receiver',
            'Password' => Hash::make('password123'),
            'Ruolo' => 'Studente',
            'Richiede_Cambio_Password' => 0
        ]);

        $this->actingAs($prof);

        $response = $this->postJson('/notifiche/invia-utente', [
            'ID_Utente_Destinatario' => $student->ID_Utente,
            'Titolo' => 'Comunicazione Importante',
            'Messaggio' => 'Test di messaggistica tracciato',
            'Tipo' => 'info'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Messaggio inviato con successo!'
                 ]);

        $this->assertDatabaseHas('notifica', [
            'ID_Utente_Mittente' => $prof->ID_Utente,
            'ID_Utente_Destinatario' => $student->ID_Utente,
            'Titolo' => 'Comunicazione Importante',
            'Messaggio' => 'Test di messaggistica tracciato'
        ]);
    }
}
