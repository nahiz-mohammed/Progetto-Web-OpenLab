<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Aula;
use App\Models\Prenotazione;
use App\Models\Corso;
use App\Models\User;
use App\Models\IscrizioneCorso;
use App\Models\ConfermaPresenza;
use App\Models\Notifica;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    // Aule 
    public function getAule()
    {
        return response()->json(Aula::all());
    }

    public function createAula(Request $request)
    {
        $request->validate([
            'Nome_Aula' => 'required|string|max:100',
            'Tipologia_Aula' => 'required|string|max:100',
            'Capienza' => 'required|integer|min:1',
            'Stato' => 'required|in:Disponibile,Manutenzione',
        ]);

        $aula = Aula::create([
            'Nome_Aula' => $request->Nome_Aula,
            'Tipologia_Aula' => $request->Tipologia_Aula,
            'Capienza' => $request->Capienza,
            'Stato' => $request->Stato,
        ]);

        return response()->json(['success' => true, 'aula' => $aula]);
    }

    public function updateAula(Request $request, $id)
    {
        $request->validate([
            'Nome_Aula' => 'required|string|max:100',
            'Tipologia_Aula' => 'required|string|max:100',
            'Capienza' => 'required|integer|min:1',
            'Stato' => 'required|in:Disponibile,Manutenzione',
        ]);

        $aula = Aula::findOrFail($id);
        $aula->update([
            'Nome_Aula' => $request->Nome_Aula,
            'Tipologia_Aula' => $request->Tipologia_Aula,
            'Capienza' => $request->Capienza,
            'Stato' => $request->Stato,
        ]);

        return response()->json(['success' => true, 'aula' => $aula]);
    }

    // Prenotazioni
    public function getPrenotazioni()
    {
        $prenotazioni = Prenotazione::with(['corso.professore', 'aula'])->get();
        return response()->json($prenotazioni);
    }

    public function updatePrenotazione(Request $request, $id)
    {
        $request->validate([
            'Data' => 'required|date',
            'Ora_Inizio' => 'required',
            'Ora_Fine' => 'required',
            'ID_Aula' => 'required|integer|exists:aula,ID_Aula',
        ]);

        $ora_inizio = $request->Ora_Inizio;
        $ora_fine = $request->Ora_Fine;

        $start_minutes = date('i', strtotime($ora_inizio));
        $end_minutes = date('i', strtotime($ora_fine));
        $start_hour = date('H', strtotime($ora_inizio));
        $end_hour = date('H', strtotime($ora_fine));

        if ($start_minutes !== '00' || $end_minutes !== '00') {
            return response()->json(['success' => false, 'message' => "Le prenotazioni devono iniziare e terminare esattamente all'ora (es. 08:00, 09:00)."], 400);
        }

        if ($start_hour < 8 || $end_hour > 20 || ($end_hour == 20 && $end_minutes !== '00')) {
            return response()->json(['success' => false, 'message' => "Le prenotazioni sono consentite solo tra le 08:00 e le 20:00."], 400);
        }

        if ($ora_inizio >= $ora_fine) {
            return response()->json(['success' => false, 'message' => "L'ora di inizio deve essere precedente all'ora di fine."], 400);
        }

        // Verifica conflitto di prenotazione
        $conflitto = Prenotazione::where('ID_Aula', $request->ID_Aula)
            ->where('Data', $request->Data)
            ->where('ID_Prenotazione', '!=', $id)
            ->where(function ($query) use ($ora_inizio, $ora_fine) {
                $query->where(function ($q) use ($ora_inizio, $ora_fine) {
                    $q->where('Ora_Inizio', '<', $ora_fine)
                      ->where('Ora_Fine', '>', $ora_inizio);
                });
            })->exists();

        if ($conflitto) {
            return response()->json(['success' => false, 'message' => "L'aula selezionata è già occupata in questa fascia oraria."], 400);
        }

        $prenotazione = Prenotazione::with(['corso', 'aula'])->findOrFail($id);
        $vecchiaData = date('d/m/Y', strtotime($prenotazione->Data));

        $prenotazione->update([
            'Data' => $request->Data,
            'Ora_Inizio' => $ora_inizio,
            'Ora_Fine' => $ora_fine,
            'ID_Aula' => $request->ID_Aula,
        ]);

        $prenotazione->load(['corso', 'aula']);

        // Trova gli studenti approvati
        $studentiIds = \App\Models\IscrizioneCorso::where('ID_Corso', $prenotazione->ID_Corso)
            ->where('Stato', 'Approvato')
            ->pluck('ID_Studente');

        $nuovaData = date('d/m/Y', strtotime($prenotazione->Data));
        $oraInizioFormatted = substr($prenotazione->Ora_Inizio, 0, 5);
        $oraFineFormatted = substr($prenotazione->Ora_Fine, 0, 5);

        foreach ($studentiIds as $studenteId) {
            \App\Models\Notifica::create([
                'ID_Utente_Destinatario' => $studenteId,
                'Titolo' => 'Lezione Modificata',
                'Messaggio' => "La lezione del corso '{$prenotazione->corso->Nome}' (originariamente prevista il {$vecchiaData}) è stata modificata: nuova data {$nuovaData} ({$oraInizioFormatted} - {$oraFineFormatted}) nell'aula '{$prenotazione->aula->Nome_Aula}'.",
                'Tipo' => 'warning',
                'Letta' => 0
            ]);
        }

        return response()->json(['success' => true, 'prenotazione' => $prenotazione]);
    }

    public function deletePrenotazione($id)
    {
        $prenotazione = Prenotazione::with(['corso', 'aula'])->findOrFail($id);
        
        // Trova gli studenti approvati
        $studentiIds = \App\Models\IscrizioneCorso::where('ID_Corso', $prenotazione->ID_Corso)
            ->where('Stato', 'Approvato')
            ->pluck('ID_Studente');

        $dataFormatted = date('d/m/Y', strtotime($prenotazione->Data));
        $oraInizioFormatted = substr($prenotazione->Ora_Inizio, 0, 5);
        $oraFineFormatted = substr($prenotazione->Ora_Fine, 0, 5);

        foreach ($studentiIds as $studenteId) {
            \App\Models\Notifica::create([
                'ID_Utente_Destinatario' => $studenteId,
                'Titolo' => 'Lezione Cancellata',
                'Messaggio' => "La lezione del corso '{$prenotazione->corso->Nome}' in data {$dataFormatted} ({$oraInizioFormatted} - {$oraFineFormatted}) è stata cancellata dall'amministratore.",
                'Tipo' => 'danger',
                'Letta' => 0
            ]);
        }

        // Elimina le conferme di presenza
        \App\Models\ConfermaPresenza::where('ID_Prenotazione', $id)->delete();

        // Elimina la prenotazione
        $prenotazione->delete();

        return response()->json(['success' => true, 'message' => 'Prenotazione cancellata con successo.']);
    }

    // Stats
    public function getStats()
    {
        $totalAule = Aula::count();
        $auleAttive = Aula::where('Stato', 'Disponibile')->count();
        $utentiTotali = User::count();
        $professori = User::where('Ruolo', 'Professore')->count();
        $studenti = User::where('Ruolo', 'Studente')->count();
        $prenotazioniOggi = Prenotazione::whereDate('Data', \Carbon\Carbon::today())->count();
        $corsiAttivi = Corso::count();

        return response()->json([
            'total_aule' => $totalAule,
            'aule_attive' => $auleAttive,
            'utenti_totali' => $utentiTotali,
            'professori' => $professori,
            'studenti' => $studenti,
            'prenotazioni_oggi' => $prenotazioniOggi,
            'corsi_attivi' => $corsiAttivi
        ]);
    }

    // Toggle stato aula
    public function toggleStatoAula($id)
    {
        $aula = Aula::findOrFail($id);
        $aula->Stato = ($aula->Stato === 'Disponibile') ? 'Manutenzione' : 'Disponibile';
        $aula->save();

        return response()->json(['success' => true, 'stato' => $aula->Stato]);
    }

    // Delete aula
    public function deleteAula($id)
    {
        $aula = Aula::findOrFail($id);
        
        // Trova tutte le prenotazioni in questa aula per notificare gli studenti ed eliminarle
        $prenotazioni = Prenotazione::where('ID_Aula', $id)->get();
        foreach ($prenotazioni as $p) {
            // Trova gli studenti da notificare
            $studentiIds = IscrizioneCorso::where('ID_Corso', $p->ID_Corso)
                ->where('Stato', 'Approvato')
                ->pluck('ID_Studente');
                
            $dataFormatted = date('d/m/Y', strtotime($p->Data));
            $oraInizioFormatted = substr($p->Ora_Inizio, 0, 5);
            $oraFineFormatted = substr($p->Ora_Fine, 0, 5);

            foreach ($studentiIds as $studenteId) {
                Notifica::create([
                    'ID_Utente_Destinatario' => $studenteId,
                    'Titolo' => 'Lezione Annullata (Aula Rimossa)',
                    'Messaggio' => "La lezione del corso '{$p->corso->Nome}' in data {$dataFormatted} ({$oraInizioFormatted} - {$oraFineFormatted}) è stata cancellata perché l'aula '{$aula->Nome_Aula}' è stata rimossa dal sistema.",
                    'Tipo' => 'danger',
                    'Letta' => 0
                ]);
            }
            
            // Elimina le conferme di presenza per questa prenotazione
            ConfermaPresenza::where('ID_Prenotazione', $p->ID_Prenotazione)->delete();
            $p->delete();
        }
        
        $aula->delete();
        return response()->json(['success' => true, 'message' => 'Aula rimossa con successo.']);
    }

    // Get utenti
    public function getUtenti()
    {
        $currentUserId = Auth::id();
        $utenti = User::all()->map(function ($u) use ($currentUserId) {
            $u->is_self = ($u->ID_Utente == $currentUserId);
            return $u;
        });
        return response()->json($utenti);
    }

    // Create utente
    public function createUtente(Request $request)
    {
        $request->validate([
            'Username' => 'required|string|unique:utente,Username|max:100',
            'Password' => 'required|string|min:6',
            'Ruolo' => 'required|in:Admin,Professore,Studente',
            'Richiede_Cambio_Password' => 'integer|in:0,1'
        ]);

        $user = User::create([
            'Username' => $request->Username,
            'Password' => Hash::make($request->Password),
            'Ruolo' => $request->Ruolo,
            'Richiede_Cambio_Password' => $request->has('Richiede_Cambio_Password') ? (int)$request->Richiede_Cambio_Password : 0,
        ]);

        return response()->json(['success' => true, 'utente' => $user]);
    }

    // Toggle Reset Password
    public function toggleResetUtente($id)
    {
        $user = User::findOrFail($id);
        if ($user->ID_Utente == Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non puoi forzare il cambio password su te stesso da qui.'], 400);
        }
        $user->Richiede_Cambio_Password = $user->Richiede_Cambio_Password ? 0 : 1;
        $user->save();

        return response()->json(['success' => true, 'richiede_cambio_password' => $user->Richiede_Cambio_Password]);
    }

    // Reset password
    public function resetPasswordUtente(Request $request, $id)
    {
        $request->validate([
            'Password' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->Password = Hash::make($request->Password);
        // Forza il cambio password al prossimo accesso
        $user->Richiede_Cambio_Password = 1;
        $user->save();

        return response()->json(['success' => true, 'message' => "Password reimpostata con successo per l'utente '{$user->Username}'."]);
    }

    // Delete utente
    public function deleteUtente($id)
    {
        $user = User::findOrFail($id);

        if ($user->ID_Utente == Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non puoi eliminare il tuo stesso account amministratore.'], 400);
        }

        // Pulizia dei dati correlati in base al ruolo
        if ($user->Ruolo === 'Studente') {
            // Elimina le notifiche inviate a questo studente
            Notifica::where('ID_Utente_Destinatario', $id)->delete();
            // Elimina le conferme di presenza
            ConfermaPresenza::where('ID_Studente', $id)->delete();
            // Elimina le iscrizioni ai corsi
            IscrizioneCorso::where('ID_Studente', $id)->delete();
        } elseif ($user->Ruolo === 'Professore') {
            // Elimina le notifiche inviate a questo professore
            Notifica::where('ID_Utente_Destinatario', $id)->delete();
            
            // Ottieni tutti i corsi appartenenti a questo professore
            $corsi = Corso::where('ID_Professore', $id)->get();
            foreach ($corsi as $corso) {
                // Elimina tutte le prenotazioni per questo corso
                $prenotazioni = Prenotazione::where('ID_Corso', $corso->ID_Corso)->get();
                foreach ($prenotazioni as $p) {
                    // Elimina le conferme di presenza
                    ConfermaPresenza::where('ID_Prenotazione', $p->ID_Prenotazione)->delete();
                    $p->delete();
                }
                // Elimina tutte le iscrizioni ai corsi
                IscrizioneCorso::where('ID_Corso', $corso->ID_Corso)->delete();
                // Elimina il corso
                $corso->delete();
            }
        } else {
            // Elimina le notifiche inviate a questo amministratore
            Notifica::where('ID_Utente_Destinatario', $id)->delete();
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Utente eliminato con successo.']);
    }
}

