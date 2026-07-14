<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Corso;
use App\Models\Aula;
use App\Models\Prenotazione;
use App\Models\IscrizioneCorso;
use App\Models\ConfermaPresenza;

class ProfessoreController extends Controller
{
    public function dashboard()
    {
        return view('professore.dashboard');
    }

    public function getCorsi()
    {
        $corsi = Corso::where('ID_Professore', Auth::id())->get();
        return response()->json($corsi);
    }

    public function addCorso(Request $request)
    {
        $request->validate([
            'Nome' => 'required|string|max:150|unique:corso,Nome',
            'Tipologia_Materia' => 'required|string|max:100',
        ], [
            'Nome.unique' => 'Un corso con questo nome è già registrato nel sistema.',
        ]);

        $corso = Corso::create([
            'Nome' => $request->Nome,
            'Tipologia_Materia' => $request->Tipologia_Materia,
            'ID_Professore' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'corso' => $corso]);
    }

    public function deleteCorso($id)
    {
        $corso = Corso::findOrFail($id);
        if ($corso->ID_Professore !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato.'], 403);
        }

        // Elimina in cascata le prenotazioni del corso e le relative conferme presenza
        $prenotazioniIds = Prenotazione::where('ID_Corso', $corso->ID_Corso)->pluck('ID_Prenotazione');
        ConfermaPresenza::whereIn('ID_Prenotazione', $prenotazioniIds)->delete();
        Prenotazione::where('ID_Corso', $corso->ID_Corso)->delete();

        // Elimina le iscrizioni al corso
        IscrizioneCorso::where('ID_Corso', $corso->ID_Corso)->delete();

        $corso->delete();

        return response()->json(['success' => true]);
    }

    public function getAuleIdonee(Request $request)
    {
        $request->validate([
            'ID_Corso' => 'nullable|integer|exists:corso,ID_Corso',
        ]);

        // Recupera tutte le aule (comprese quelle in manutenzione)
        $aule = Aula::all();

        return response()->json($aule);
    }

    public function getPrenotazioni()
    {
        $prenotazioni = Prenotazione::with(['corso.professore', 'aula'])->get();
        return response()->json($prenotazioni);
    }

    public function createPrenotazione(Request $request)
    {
        $request->validate([
            'Data' => 'required|date',
            'Ora_Inizio' => 'required',
            'Ora_Fine' => 'required',
            'ID_Corso' => 'required|integer|exists:corso,ID_Corso',
            'ID_Aula' => 'required|integer|exists:aula,ID_Aula',
        ]);

        $aula = Aula::findOrFail($request->ID_Aula);
        if ($aula->Stato === 'Manutenzione') {
            return response()->json(['success' => false, 'message' => "Non è possibile prenotare un'aula in manutenzione."], 400);
        }

        $today = date('Y-m-d');
        if ($request->Data <= $today) {
            return response()->json(['success' => false, 'message' => "Non è possibile prenotare lezioni per oggi o nel passato. La prenotazione deve essere effettuata a partire da domani."], 400);
        }

        $corso = Corso::findOrFail($request->ID_Corso);
        if ($corso->ID_Professore !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato per questo corso.'], 403);
        }

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

        // Verifica conflitti di prenotazione
        $conflitto = Prenotazione::where('ID_Aula', $request->ID_Aula)
            ->where('Data', $request->Data)
            ->where(function ($query) use ($ora_inizio, $ora_fine) {
                $query->where('Ora_Inizio', '<', $ora_fine)
                      ->where('Ora_Fine', '>', $ora_inizio);
            })->exists();

        if ($conflitto) {
            return response()->json(['success' => false, 'message' => "L'aula è già occupata in questa fascia oraria."], 400);
        }

        $prenotazione = Prenotazione::create([
            'Data' => $request->Data,
            'Ora_Inizio' => $ora_inizio,
            'Ora_Fine' => $ora_fine,
            'ID_Corso' => $request->ID_Corso,
            'ID_Aula' => $request->ID_Aula,
        ]);

        $prenotazione->load(['corso', 'aula']);

        // Trova gli studenti approvati
        $studentiIds = IscrizioneCorso::where('ID_Corso', $prenotazione->ID_Corso)
            ->where('Stato', 'Approvato')
            ->pluck('ID_Studente');

        $dataFormatted = date('d/m/Y', strtotime($prenotazione->Data));
        $oraInizioFormatted = substr($prenotazione->Ora_Inizio, 0, 5);
        $oraFineFormatted = substr($prenotazione->Ora_Fine, 0, 5);

        foreach ($studentiIds as $studenteId) {
            \App\Models\Notifica::create([
                'ID_Utente_Destinatario' => $studenteId,
                'Titolo' => 'Nuova Lezione',
                'Messaggio' => "È stata programmata una nuova lezione per il corso '{$prenotazione->corso->Nome}': data {$dataFormatted} ({$oraInizioFormatted} - {$oraFineFormatted}) nell'aula '{$prenotazione->aula->Nome_Aula}'.",
                'Tipo' => 'success',
                'Letta' => 0
            ]);
        }

        return response()->json(['success' => true, 'prenotazione' => $prenotazione]);
    }

    public function updatePrenotazione(Request $request, $id)
    {
        $request->validate([
            'Data' => 'required|date',
            'Ora_Inizio' => 'required',
            'Ora_Fine' => 'required',
            'ID_Aula' => 'required|integer|exists:aula,ID_Aula',
        ]);

        $aula = Aula::findOrFail($request->ID_Aula);
        if ($aula->Stato === 'Manutenzione') {
            return response()->json(['success' => false, 'message' => "Non è possibile spostare una lezione in un'aula in manutenzione."], 400);
        }

        $prenotazione = Prenotazione::with(['corso', 'aula'])->findOrFail($id);
        if ($prenotazione->corso->ID_Professore !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato.'], 403);
        }

        $today = date('Y-m-d');
        if ($prenotazione->Data <= $today) {
            return response()->json(['success' => false, 'message' => "Non è possibile modificare lezioni passate o di oggi."], 400);
        }
        if ($request->Data <= $today) {
            return response()->json(['success' => false, 'message' => "La nuova data della lezione deve essere a partire da domani."], 400);
        }

        $vecchiaData = date('d/m/Y', strtotime($prenotazione->Data));
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

        // Verifica conflitti di prenotazione
        $conflitto = Prenotazione::where('ID_Aula', $request->ID_Aula)
            ->where('Data', $request->Data)
            ->where('ID_Prenotazione', '!=', $id)
            ->where(function ($query) use ($ora_inizio, $ora_fine) {
                $query->where('Ora_Inizio', '<', $ora_fine)
                      ->where('Ora_Fine', '>', $ora_inizio);
            })->exists();

        if ($conflitto) {
            return response()->json(['success' => false, 'message' => "L'aula è già occupata in questa fascia oraria."], 400);
        }

        $prenotazione->update([
            'Data' => $request->Data,
            'Ora_Inizio' => $ora_inizio,
            'Ora_Fine' => $ora_fine,
            'ID_Aula' => $request->ID_Aula,
        ]);

        $prenotazione->load(['corso', 'aula']);

        // Trova gli studenti approvati
        $studentiIds = IscrizioneCorso::where('ID_Corso', $prenotazione->ID_Corso)
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
        if ($prenotazione->corso->ID_Professore !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato.'], 403);
        }

        // Trova gli studenti approvati
        $studentiIds = IscrizioneCorso::where('ID_Corso', $prenotazione->ID_Corso)
            ->where('Stato', 'Approvato')
            ->pluck('ID_Studente');

        $dataFormatted = date('d/m/Y', strtotime($prenotazione->Data));
        $oraInizioFormatted = substr($prenotazione->Ora_Inizio, 0, 5);
        $oraFineFormatted = substr($prenotazione->Ora_Fine, 0, 5);

        foreach ($studentiIds as $studenteId) {
            \App\Models\Notifica::create([
                'ID_Utente_Destinatario' => $studenteId,
                'Titolo' => 'Lezione Cancellata',
                'Messaggio' => "La lezione del corso '{$prenotazione->corso->Nome}' in data {$dataFormatted} ({$oraInizioFormatted} - {$oraFineFormatted}) è stata cancellata dal docente.",
                'Tipo' => 'danger',
                'Letta' => 0
            ]);
        }

        // Elimina le conferme di presenza
        \App\Models\ConfermaPresenza::where('ID_Prenotazione', $id)->delete();

        // Elimina la prenotazione
        $prenotazione->delete();
        
        return response()->json(['success' => true, 'message' => 'Prenotazione cancellata.']);
    }

    public function getIscrizioniPendenti()
    {
        $iscrizioni = IscrizioneCorso::with(['studente', 'corso'])
            ->whereHas('corso', function($query) {
                $query->where('ID_Professore', Auth::id());
            })
            ->where('Stato', 'In attesa')
            ->get();

        return response()->json($iscrizioni);
    }

    public function approvaIscrizione($id)
    {
        $iscrizione = IscrizioneCorso::with('corso')->findOrFail($id);
        if ($iscrizione->corso->ID_Professore !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato.'], 403);
        }

        $iscrizione->update(['Stato' => 'Approvato']);
        return response()->json(['success' => true, 'message' => 'Iscrizione approvata.']);
    }

    public function rifiutaIscrizione($id)
    {
        $iscrizione = IscrizioneCorso::with('corso')->findOrFail($id);
        if ($iscrizione->corso->ID_Professore !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato.'], 403);
        }

        $iscrizione->update(['Stato' => 'Rifiutato']);
        return response()->json(['success' => true, 'message' => 'Iscrizione rifiutata.']);
    }

    public function approvaTutteIscrizioni()
    {
        $iscrizioni = IscrizioneCorso::whereHas('corso', function($query) {
            $query->where('ID_Professore', Auth::id());
        })
        ->where('Stato', 'In attesa')
        ->get();

        foreach ($iscrizioni as $iscrizione) {
            $iscrizione->update(['Stato' => 'Approvato']);
        }

        return response()->json(['success' => true, 'message' => 'Tutte le iscrizioni in attesa sono state approvate.']);
    }

    public function getIscrizioniGestite()
    {
        $iscrizioni = IscrizioneCorso::with(['studente', 'corso'])
            ->whereHas('corso', function($query) {
                $query->where('ID_Professore', Auth::id());
            })
            ->whereIn('Stato', ['Approvato', 'Rifiutato'])
            ->get();

        return response()->json($iscrizioni);
    }
}
