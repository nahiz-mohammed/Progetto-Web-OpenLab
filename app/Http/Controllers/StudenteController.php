<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Corso;
use App\Models\IscrizioneCorso;
use App\Models\Prenotazione;
use App\Models\ConfermaPresenza;
use Carbon\Carbon;

class StudenteController extends Controller
{
    public function dashboard()
    {
        return view('studente.dashboard');
    }

    public function cercaCorsi(Request $request)
    {
        $query = $request->query('query', '');

        // Recupera i corsi, includendo le informazioni del professore, filtrando per nome se la query è presente
        $corsi = Corso::with('professore')
            ->where('Nome', 'like', '%' . $query . '%')
            ->get();

        // Includi lo stato dell'iscrizione per lo studente corrente
        $corsiMapped = $corsi->map(function ($corso) {
            $iscrizione = IscrizioneCorso::where('ID_Studente', Auth::id())
                ->where('ID_Corso', $corso->ID_Corso)
                ->first();

            $corso->stato_iscrizione = $iscrizione ? $iscrizione->Stato : 'Non iscritto';
            return $corso;
        });

        return response()->json($corsiMapped);
    }

    public function iscriviti(Request $request)
    {
        $request->validate([
            'ID_Corso' => 'required|integer|exists:corso,ID_Corso',
        ]);

        $exists = IscrizioneCorso::where('ID_Studente', Auth::id())
            ->where('ID_Corso', $request->ID_Corso)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Richiesta di iscrizione già presentata.'], 400);
        }

        $iscrizione = IscrizioneCorso::create([
            'ID_Studente' => Auth::id(),
            'ID_Corso' => $request->ID_Corso,
            'Stato' => 'In attesa',
            'Data_Richiesta' => Carbon::today()->toDateString(),
        ]);

        return response()->json(['success' => true, 'message' => 'Iscrizione in attesa di approvazione.']);
    }

    public function getCorsiAttivi()
    {
        $iscrizioni = IscrizioneCorso::with(['corso.professore'])
            ->where('ID_Studente', Auth::id())
            ->get();

        return response()->json($iscrizioni);
    }

    public function getLezioniProgrammate()
    {
        // Ottieni tutti gli ID dei corsi a cui lo studente è iscritto ed approvato
        $corsiApprovatiIds = IscrizioneCorso::where('ID_Studente', Auth::id())
            ->where('Stato', 'Approvato')
            ->pluck('ID_Corso');

        $startOfWeek = Carbon::now('Europe/Rome')->startOfWeek()->toDateString();

        // Ottieni tutte le prossime prenotazioni per questi corsi
        $lezioni = Prenotazione::with(['corso.professore', 'aula'])
            ->whereIn('ID_Corso', $corsiApprovatiIds)
            ->where('Data', '>=', $startOfWeek)
            ->orderBy('Data', 'asc')
            ->orderBy('Ora_Inizio', 'asc')
            ->get();

        // Associa i dettagli della conferma
        $lezioniMapped = $lezioni->map(function ($lezione) {
            $presenzeConfermate = ConfermaPresenza::where('ID_Prenotazione', $lezione->ID_Prenotazione)
                ->where('Confermata', 1)
                ->count();

            $giaConfermato = ConfermaPresenza::where('ID_Prenotazione', $lezione->ID_Prenotazione)
                ->where('ID_Studente', Auth::id())
                ->where('Confermata', 1)
                ->exists();

            return [
                'ID_Prenotazione' => $lezione->ID_Prenotazione,
                'Data' => $lezione->Data,
                'Ora_Inizio' => $lezione->Ora_Inizio,
                'Ora_Fine' => $lezione->Ora_Fine,
                'ID_Corso' => $lezione->ID_Corso,
                'Corso_Nome' => $lezione->corso->Nome,
                'Prof_Username' => $lezione->corso->professore->Username,
                'Aula_Nome' => $lezione->aula->Nome_Aula,
                'Aula_Tipologia' => $lezione->aula->Tipologia_Aula,
                'Capienza_Max' => $lezione->aula->Capienza,
                'Capienza_Attuale' => $presenzeConfermate,
                'Gia_Confermato' => $giaConfermato
            ];
        });

        return response()->json($lezioniMapped);
    }

    public function getProssimaLezione($id_corso)
    {
        // Verifica che lo studente sia approvato per questo corso
        $iscrizione = IscrizioneCorso::where('ID_Studente', Auth::id())
            ->where('ID_Corso', $id_corso)
            ->where('Stato', 'Approvato')
            ->first();

        if (!$iscrizione) {
            return response()->json(['success' => false, 'message' => 'Non sei iscritto o approvato per questo corso.'], 403);
        }

        $startOfWeek = Carbon::now('Europe/Rome')->startOfWeek()->toDateString();

        // Trova la prossima prenotazione che inizia oggi o in futuro
        $prossimaLezione = Prenotazione::with('aula')
            ->where('ID_Corso', $id_corso)
            ->where('Data', '>=', $startOfWeek)
            ->orderBy('Data', 'asc')
            ->orderBy('Ora_Inizio', 'asc')
            ->first();

        if (!$prossimaLezione) {
            return response()->json(['success' => false, 'message' => 'Nessuna lezione programmata.']);
        }

        // Conta quanti studenti hanno confermato la loro presenza per questa lezione
        $presenzeConfermate = ConfermaPresenza::where('ID_Prenotazione', $prossimaLezione->ID_Prenotazione)
            ->where('Confermata', 1)
            ->count();

        // Controlla se lo studente corrente ha già confermato la presenza
        $giaConfermato = ConfermaPresenza::where('ID_Prenotazione', $prossimaLezione->ID_Prenotazione)
            ->where('ID_Studente', Auth::id())
            ->where('Confermata', 1)
            ->exists();

        return response()->json([
            'success' => true,
            'lezione' => $prossimaLezione,
            'capienza_max' => $prossimaLezione->aula->Capienza,
            'capienza_attuale' => $presenzeConfermate,
            'gia_confermato' => $giaConfermato
        ]);
    }

    public function confermaPresenza(Request $request)
    {
        $request->validate([
            'ID_Prenotazione' => 'required|integer|exists:prenotazione,ID_Prenotazione',
        ]);

        $prenotazione = Prenotazione::with('aula')->findOrFail($request->ID_Prenotazione);

        // Verifica che lo studente sia iscritto ed approvato nel corso
        $iscrizione = IscrizioneCorso::where('ID_Studente', Auth::id())
            ->where('ID_Corso', $prenotazione->ID_Corso)
            ->where('Stato', 'Approvato')
            ->first();

        if (!$iscrizione) {
            return response()->json(['success' => false, 'message' => 'Non sei iscritto o approvato per il corso di questa lezione.'], 403);
        }

        // Verifica la capienza dell'aula prima di confermare (tranne se stanno aggiornando una conferma già esistente)
        $giaConfermato = ConfermaPresenza::where('ID_Prenotazione', $request->ID_Prenotazione)
            ->where('ID_Studente', Auth::id())
            ->where('Confermata', 1)
            ->exists();

        if (!$giaConfermato) {
            $presenzeConfermate = ConfermaPresenza::where('ID_Prenotazione', $request->ID_Prenotazione)
                ->where('Confermata', 1)
                ->count();

            if ($presenzeConfermate >= $prenotazione->aula->Capienza) {
                return response()->json(['success' => false, 'message' => 'Capienza massima raggiunta per questa aula.'], 400);
            }
        }

        // Crea o aggiorna la conferma di presenza
        ConfermaPresenza::updateOrCreate(
            [
                'ID_Studente' => Auth::id(),
                'ID_Prenotazione' => $request->ID_Prenotazione,
            ],
            [
                'Confermata' => 1,
                'Timestamp_Conferma' => Carbon::now()->toDateTimeString(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Presenza confermata con successo!']);
    }
}
