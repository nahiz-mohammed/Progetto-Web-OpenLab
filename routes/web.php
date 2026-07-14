<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfessoreController;
use App\Http\Controllers\StudenteController;
use App\Http\Controllers\NotificaController;

// Auth routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change.password');
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth', 'password.change'])->group(function () {
    
    // Notification Routes
    Route::get('/notifiche', [NotificaController::class, 'getNotifiche']);
    Route::get('/notifiche/corsi', [NotificaController::class, 'getCorsiDestinatari']);
    Route::get('/notifiche/destinatari', [NotificaController::class, 'getDestinatari']);
    Route::post('/notifiche/letta/{id}', [NotificaController::class, 'segnaComeLetta']);
    Route::post('/notifiche/letta-tutte', [NotificaController::class, 'segnaTutteComeLette']);
    Route::post('/notifiche/invia', [NotificaController::class, 'inviaNotificaCorso']);
    Route::post('/notifiche/invia-utente', [NotificaController::class, 'inviaNotificaUtente']);

    // Admin Routes
    Route::middleware(['role.admin'])->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard']);
        Route::get('/admin/stats', [AdminController::class, 'getStats']);
        
        // Aule
        Route::get('/admin/aule', [AdminController::class, 'getAule']);
        Route::post('/admin/aule', [AdminController::class, 'createAula']);
        Route::put('/admin/aule/{id}', [AdminController::class, 'updateAula']);
        Route::post('/admin/aule/{id}/toggle-stato', [AdminController::class, 'toggleStatoAula']);
        Route::delete('/admin/aule/{id}', [AdminController::class, 'deleteAula']);
        
        // Prenotazioni
        Route::get('/admin/prenotazioni', [AdminController::class, 'getPrenotazioni']);
        Route::put('/admin/prenotazioni/{id}', [AdminController::class, 'updatePrenotazione']);
        Route::delete('/admin/prenotazioni/{id}', [AdminController::class, 'deletePrenotazione']);

        // Utenti
        Route::get('/admin/utenti', [AdminController::class, 'getUtenti']);
        Route::post('/admin/utenti', [AdminController::class, 'createUtente']);
        Route::post('/admin/utenti/{id}/toggle-reset', [AdminController::class, 'toggleResetUtente']);
        Route::post('/admin/utenti/{id}/reset-password', [AdminController::class, 'resetPasswordUtente']);
        Route::delete('/admin/utenti/{id}', [AdminController::class, 'deleteUtente']);
    });

        // Professor Routes
    Route::middleware(['role.professore'])->group(function () {
        Route::get('/professore', [ProfessoreController::class, 'dashboard']);
        Route::get('/professore/corsi', [ProfessoreController::class, 'getCorsi']);
        Route::post('/professore/corsi', [ProfessoreController::class, 'addCorso']);
        Route::delete('/professore/corsi/{id}', [ProfessoreController::class, 'deleteCorso']);
        Route::get('/professore/aule-idonee', [ProfessoreController::class, 'getAuleIdonee']);
        Route::get('/professore/prenotazioni', [ProfessoreController::class, 'getPrenotazioni']);
        Route::post('/professore/prenotazioni', [ProfessoreController::class, 'createPrenotazione']);
        Route::put('/professore/prenotazioni/{id}', [ProfessoreController::class, 'updatePrenotazione']);
        Route::delete('/professore/prenotazioni/{id}', [ProfessoreController::class, 'deletePrenotazione']);
        Route::get('/professore/iscrizioni-pendenti', [ProfessoreController::class, 'getIscrizioniPendenti']);
        Route::get('/professore/iscrizioni-gestite', [ProfessoreController::class, 'getIscrizioniGestite']);
        Route::post('/professore/iscrizioni/approva-tutte', [ProfessoreController::class, 'approvaTutteIscrizioni']);
        Route::post('/professore/iscrizioni/{id}/approva', [ProfessoreController::class, 'approvaIscrizione']);
        Route::post('/professore/iscrizioni/{id}/rifiuta', [ProfessoreController::class, 'rifiutaIscrizione']);
    });

    // Student Routes
    Route::middleware(['role.studente'])->group(function () {
        Route::get('/studente', [StudenteController::class, 'dashboard']);
        Route::get('/studente/ricerca-corsi', [StudenteController::class, 'cercaCorsi']);
        Route::post('/studente/iscriviti', [StudenteController::class, 'iscriviti']);
        Route::get('/studente/corsi-attivi', [StudenteController::class, 'getCorsiAttivi']);
        Route::get('/studente/lezioni-programmate', [StudenteController::class, 'getLezioniProgrammate']);
        Route::get('/studente/prossima-lezione/{id_corso}', [StudenteController::class, 'getProssimaLezione']);
        Route::post('/studente/conferma-presenza', [StudenteController::class, 'confermaPresenza']);
    });
});
