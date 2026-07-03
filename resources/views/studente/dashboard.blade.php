@extends('layouts.app')

@section('title', 'Dashboard Studente - OpenLab')

@section('content')
<div class="row mb-4 animate-fade-in">
    <div class="col-12">
        <div class="glass-card p-4">
            <h1 class="text-white mb-1">Pannello Studente</h1>
            <p class="text-secondary mb-0">Gestisci le tue lezioni in programma e cerca nuovi corsi da seguire</p>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4 animate-fade-in" id="studentTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="attivita-tab" data-bs-toggle="tab" data-bs-target="#attivita-pane" type="button" role="tab">
            <i class="fa-solid fa-calendar-days me-2 text-accent"></i>Le Mie Attività
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="trova-tab" data-bs-toggle="tab" data-bs-target="#trova-pane" type="button" role="tab">
            <i class="fa-solid fa-magnifying-glass me-2 text-warning"></i>Trova Corsi
        </button>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content animate-fade-in" id="studentTabsContent">
    
    <!-- Tab Attività (Lezioni + Corsi Attivi in affiancamento) -->
    <div class="tab-pane fade show active" id="attivita-pane" role="tabpanel">
        <div class="row g-4">
            <!-- Lezioni in Programma (Sinistra - 2/3) -->
            <div class="col-lg-8">
                <div class="glass-card p-4 h-100">
                    <h3 class="text-white mb-3 display-font h5"><i class="fa-solid fa-calendar-day me-2 text-accent"></i>Lezioni in Programma</h3>
                    <div class="row row-cols-1 row-cols-md-2 g-3" id="lezioni-container">
                        <!-- Caricamento dinamico lezioni -->
                        <div class="col-12 text-center text-secondary py-5">
                            <span class="spinner-border spinner-border-sm me-2"></span>Caricamento lezioni...
                        </div>
                    </div>
                </div>
            </div>

            <!-- I Miei Corsi (Destra - 1/3) -->
            <div class="col-lg-4">
                <div class="glass-card p-4 h-100">
                    <h3 class="text-white mb-3 display-font h5"><i class="fa-solid fa-graduation-cap me-2 text-success"></i>I Miei Corsi</h3>
                    <div class="d-flex flex-column gap-3" id="my-courses-list">
                        <!-- Caricamento dinamico corsi attivi -->
                        <div class="text-center text-secondary py-5">
                            <span class="spinner-border spinner-border-sm me-2"></span>Caricamento corsi...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Trova Corsi -->
    <div class="tab-pane fade" id="trova-pane" role="tabpanel">
        <div class="glass-card p-4">
            <h3 class="text-white mb-4 display-font h5">Cerca e iscriviti a nuovi corsi</h3>
            
            <div class="search-container mb-4">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="form-control py-3 search-input" id="search-corso" placeholder="Inserisci il nome del corso per filtrare (es. Elettrotecnica)...">
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-3" id="cerca-corsi-container">
                <!-- Popolato via AJAX -->
                <div class="col-12 text-center text-secondary py-5">
                    <span class="spinner-border spinner-border-sm me-2"></span>Caricamento corsi disponibili...
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inizializza i dati
        loadLezioniProgrammate();
        loadCorsiStudente();
        cercaCorsi('');

        // 1. CARICAMENTO LEZIONI IN PROGRAMMA (UNIFICATO)
        function loadLezioniProgrammate() {
            const container = $('#lezioni-container');
            
            $.ajax({
                url: '/studente/lezioni-programmate',
                method: 'GET',
                success: function(lezioni) {
                    container.empty();
                    
                    if (lezioni.length === 0) {
                        container.append(`
                            <div class="col-12 text-center py-5">
                                <i class="fa-solid fa-calendar-xmark fa-2x text-secondary mb-3"></i>
                                <h6 class="text-white">Nessuna lezione in programma</h6>
                                <p class="text-secondary small mb-0">Non ci sono attualmente prenotazioni attive per i tuoi corsi approvati.</p>
                            </div>
                        `);
                        return;
                    }

                    lezioni.forEach(lez => {
                        // Formatta data DD/MM/YYYY
                        const dateParts = lez.Data.split('-');
                        const dateFormatted = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                        
                        // Orari
                        const timeStart = lez.Ora_Inizio.substring(0, 5);
                        const timeEnd = lez.Ora_Fine.substring(0, 5);
                        
                        // Calcola capienza
                        const max = lez.Capienza_Max;
                        const curr = lez.Capienza_Attuale;
                        const pct = Math.min((curr / max) * 100, 100);
                        
                        let barClass = 'bg-success';
                        if (pct >= 90) barClass = 'bg-danger';
                        else if (pct >= 75) barClass = 'bg-warning';

                        // Pulsante conferma presenza
                        let btnHtml = '';
                        if (lez.Gia_Confermato) {
                            btnHtml = `
                                <button class="btn btn-success btn-sm w-100 py-2" disabled>
                                    <i class="fa-solid fa-check-double me-2"></i>Presenza Confermata
                                </button>
                            `;
                        } else if (curr >= max) {
                            btnHtml = `
                                <button class="btn btn-secondary btn-sm w-100 py-2" disabled>
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Aula al completo
                                </button>
                            `;
                        } else {
                            btnHtml = `
                                <button class="btn btn-accent btn-sm w-100 py-2 btn-conferma-presenza" data-id="${lez.ID_Prenotazione}">
                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                    <i class="fa-solid fa-circle-check me-2"></i>Conferma Presenza
                                </button>
                            `;
                        }

                        const card = `
                            <div class="col animate-fade-in">
                                <div class="glass-card p-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.01) !important;">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h4 class="text-white mb-0 display-font" style="font-size: 0.95rem;">${lez.Corso_Nome}</h4>
                                                <span class="text-secondary" style="font-size: 0.75rem;">Prof. ${lez.Prof_Username}</span>
                                            </div>
                                            <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem !important;">${lez.Aula_Tipologia}</span>
                                        </div>
                                        
                                        <div class="row mb-2 g-1">
                                            <div class="col-6">
                                                <span class="text-secondary" style="font-size: 0.65rem;">DATA</span>
                                                <div class="text-white fw-bold" style="font-size: 0.8rem;">
                                                    <i class="fa-regular fa-calendar me-1.5 text-accent"></i>${dateFormatted}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <span class="text-secondary" style="font-size: 0.65rem;">ORARIO</span>
                                                <div class="text-white fw-bold" style="font-size: 0.8rem;">
                                                    <i class="fa-regular fa-clock me-1.5 text-accent"></i>${timeStart} - ${timeEnd}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-2 border-top" style="border-color: rgba(255,255,255,0.06) !important;">
                                            <div class="text-white fw-bold mb-2" style="font-size: 0.8rem;">
                                                <i class="fa-solid fa-door-open me-1.5 text-success"></i>${lez.Aula_Nome}
                                            </div>
                                            
                                            <div>
                                                <div class="d-flex justify-content-between text-secondary" style="font-size: 0.7rem;">
                                                    <span>Posti occupati</span>
                                                    <span>${curr} / ${max}</span>
                                                </div>
                                                <div class="capacity-bar" style="height: 4px !important; margin: 4px 0 !important;">
                                                    <div class="capacity-fill ${barClass}" style="width: ${pct}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2.5">
                                        ${btnHtml}
                                    </div>
                                </div>
                            </div>
                        `;
                        container.append(card);
                    });
                },
                error: function() {
                    container.html(`
                        <div class="col-12 text-center text-danger py-4">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>Errore nel recupero delle lezioni.
                        </div>
                    `);
                }
            });
        }

        // Click handler per confermare la presenza
        $(document).on('click', '.btn-conferma-presenza', function() {
            const btn = $(this);
            const idPrenotazione = btn.data('id');
            const spinner = btn.find('.spinner-border');

            btn.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: '/studente/conferma-presenza',
                method: 'POST',
                data: { ID_Prenotazione: idPrenotazione },
                success: function(response) {
                    showToast('Presenza confermata con successo!');
                    loadLezioniProgrammate();
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    let msg = "Errore durante la conferma della presenza.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showToast(msg, "danger");
                }
            });
        });

        // 2. CARICAMENTO CORSI DELLO STUDENTE (I MIEI CORSI ATTIVI)
        function loadCorsiStudente() {
            const container = $('#my-courses-list');
            
            $.ajax({
                url: '/studente/corsi-attivi',
                method: 'GET',
                success: function(iscrizioni) {
                    container.empty();

                    // Mostra SOLO i corsi in cui lo studente è "Approvato"
                    const corsiApprovati = iscrizioni.filter(isc => isc.Stato === 'Approvato');

                    if (corsiApprovati.length === 0) {
                        container.append(`
                            <div class="text-center py-4 text-secondary">
                                <i class="fa-solid fa-graduation-cap fa-2x text-muted mb-2"></i>
                                <p class="mb-0 small">Non sei ancora iscritto o approvato in nessun corso.</p>
                            </div>
                        `);
                        return;
                    }

                    corsiApprovati.forEach(isc => {
                        const item = `
                            <div class="my-course-item" style="cursor: default; padding: 12px !important; background: rgba(255,255,255,0.01) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h4 class="h6 text-white mb-0 fw-bold" style="font-size: 0.85rem;">${isc.corso.Nome}</h4>
                                    <span class="badge badge-approvato small" style="font-size: 0.65rem !important;">Attivo</span>
                                </div>
                                <div class="text-secondary" style="font-size: 0.75rem;">
                                    <div>Docente: <strong>${isc.corso.professore.Username}</strong></div>
                                    <div>Materia: <strong>${isc.corso.Tipologia_Materia}</strong></div>
                                </div>
                            </div>
                        `;
                        container.append(item);
                    });
                },
                error: function() {
                    container.html(`
                        <div class="text-center text-danger py-4">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>Errore nel recupero dei corsi.
                        </div>
                    `);
                }
            });
        }

        // 3. RICERCA E ISCRIZIONE CORSI
        $('#search-corso').on('input', function() {
            cercaCorsi($(this).val());
        });

        function cercaCorsi(query) {
            const container = $('#cerca-corsi-container');

            $.ajax({
                url: '/studente/ricerca-corsi',
                method: 'GET',
                data: { query: query },
                success: function(corsi) {
                    container.empty();

                    if (corsi.length === 0) {
                        container.append('<div class="col-12 text-center text-secondary py-3">Nessun corso corrispondente trovato.</div>');
                        return;
                    }

                    corsi.forEach(corso => {
                        let btnHtml = '';
                        
                        if (corso.stato_iscrizione === 'Non iscritto') {
                            btnHtml = `<button class="btn btn-sm btn-accent btn-iscriviti w-100" data-id="${corso.ID_Corso}"><i class="fa-solid fa-plus me-1"></i> Richiedi Iscrizione</button>`;
                        } else if (corso.stato_iscrizione === 'In attesa') {
                            btnHtml = `<button class="btn btn-sm btn-outline-custom w-100 text-warning" disabled><i class="fa-regular fa-clock me-1"></i> In attesa di approvazione</button>`;
                        } else if (corso.stato_iscrizione === 'Approvato') {
                            btnHtml = `<button class="btn btn-sm btn-outline-custom w-100 text-success" disabled><i class="fa-solid fa-check me-1"></i> Iscritto (Approvato)</button>`;
                        } else if (corso.stato_iscrizione === 'Rifiutato') {
                            btnHtml = `<button class="btn btn-sm btn-outline-custom w-100 text-danger" disabled><i class="fa-solid fa-xmark me-1"></i> Richiesta Rifiutata</button>`;
                        }

                        const col = `
                            <div class="col">
                                <div class="my-course-item h-100 d-flex flex-column justify-content-between" style="cursor: default; padding: 14px !important;">
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h4 class="h6 text-white mb-0 fw-bold" style="font-size: 0.85rem;">${corso.Nome}</h4>
                                            <span class="badge bg-secondary font-monospace small" style="font-size: 0.65rem !important;">${corso.Tipologia_Materia}</span>
                                        </div>
                                        <p class="text-secondary small mb-0" style="font-size: 0.75rem;">Docente: <strong>${corso.professore.Username}</strong></p>
                                    </div>
                                    <div class="text-end mt-auto pt-2">
                                        ${btnHtml}
                                    </div>
                                </div>
                            </div>
                        `;
                        container.append(col);
                    });
                },
                error: function() {
                    container.html('<div class="col-12 text-center text-danger py-3">Errore nella ricerca dei corsi.</div>');
                }
            });
        }

        // Handler per pulsante iscriviti
        $(document).on('click', '.btn-iscriviti', function() {
            const btn = $(this);
            const idCorso = btn.data('id');
            btn.prop('disabled', true);

            $.ajax({
                url: '/studente/iscriviti',
                method: 'POST',
                data: { ID_Corso: idCorso },
                success: function(response) {
                    showToast('Richiesta d\'iscrizione inviata con successo!');
                    loadCorsiStudente();
                    cercaCorsi($('#search-corso').val());
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    let msg = "Errore durante l'invio della richiesta.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showToast(msg, "danger");
                }
            });
        });
    });
</script>
@endsection
