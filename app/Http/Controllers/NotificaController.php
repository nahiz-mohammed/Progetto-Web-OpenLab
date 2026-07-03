<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notifica;
use App\Models\IscrizioneCorso;
use App\Models\Corso;
use App\Models\User;
use Carbon\Carbon;

class NotificaController extends Controller
{
    public function getNotifiche()
    {
        $notifiche = Notifica::with('mittente')
            ->where('ID_Utente_Destinatario', Auth::id())
            ->orderBy('Data_Invio', 'desc')
            ->get();

        return response()->json($notifiche);
    }

    public function segnaComeLetta($id)
    {
        $notifica = Notifica::where('ID_Utente_Destinatario', Auth::id())
            ->where('ID_Notifica', $id)
            ->firstOrFail();

        $notifica->update(['Letta' => 1]);

        return response()->json(['success' => true]);
    }

    public function segnaTutteComeLette()
    {
        Notifica::where('ID_Utente_Destinatario', Auth::id())
            ->update(['Letta' => 1]);

        return response()->json(['success' => true]);
    }

    public function inviaNotificaCorso(Request $request)
    {
        $request->validate([
            'ID_Corso' => 'required|integer|exists:corso,ID_Corso',
            'Titolo' => 'required|string|max:255',
            'Messaggio' => 'required|string',
            'Tipo' => 'required|string|in:info,warning,success,danger'
        ]);

        $corso = Corso::findOrFail($request->ID_Corso);

        // Verifica autorizzazione (solo l'amministratore o il professore del corso possono inviare)
        if (Auth::user()->Ruolo === 'Professore' && $corso->ID_Professore !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato.'], 403);
        }

        // Ottieni tutti gli studenti approvati per questo corso
        $studentiIds = IscrizioneCorso::where('ID_Corso', $request->ID_Corso)
            ->where('Stato', 'Approvato')
            ->pluck('ID_Studente');

        if ($studentiIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Nessuno studente iscritto e approvato in questo corso.'], 400);
        }

        foreach ($studentiIds as $studenteId) {
            Notifica::create([
                'ID_Utente_Destinatario' => $studenteId,
                'ID_Utente_Mittente' => Auth::id(),
                'Titolo' => $request->Titolo,
                'Messaggio' => $request->Messaggio,
                'Tipo' => $request->Tipo,
                'Letta' => 0,
                'Data_Invio' => Carbon::now('Europe/Rome')->toDateTimeString()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Messaggio inviato con successo a tutti gli iscritti!']);
    }

    public function getCorsiDestinatari()
    {
        if (Auth::user()->Ruolo === 'Admin') {
            $corsi = Corso::all();
        } else if (Auth::user()->Ruolo === 'Professore') {
            $corsi = Corso::where('ID_Professore', Auth::id())->get();
        } else {
            $corsi = [];
        }
        return response()->json($corsi);
    }

    public function getDestinatari(Request $request)
    {
        $ruolo = $request->query('ruolo');
        if (!in_array($ruolo, ['Admin', 'Professore', 'Studente'])) {
            return response()->json([]);
        }

        $utenti = User::where('Ruolo', $ruolo)
            ->where('ID_Utente', '!=', Auth::id())
            ->orderBy('Username', 'asc')
            ->get(['ID_Utente', 'Username']);

        return response()->json($utenti);
    }

    public function inviaNotificaUtente(Request $request)
    {
        $request->validate([
            'ID_Utente_Destinatario' => 'required|integer|exists:utente,ID_Utente',
            'Titolo' => 'required|string|max:255',
            'Messaggio' => 'required|string',
            'Tipo' => 'required|string|in:info,warning,success,danger'
        ]);

        if ($request->ID_Utente_Destinatario == Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non puoi inviare un messaggio a te stesso.'], 400);
        }

        $notifica = Notifica::create([
            'ID_Utente_Destinatario' => $request->ID_Utente_Destinatario,
            'ID_Utente_Mittente' => Auth::id(),
            'Titolo' => $request->Titolo,
            'Messaggio' => $request->Messaggio,
            'Tipo' => $request->Tipo,
            'Letta' => 0,
            'Data_Invio' => Carbon::now('Europe/Rome')->toDateTimeString()
        ]);

        return response()->json(['success' => true, 'message' => 'Messaggio inviato con successo!']);
    }
}
