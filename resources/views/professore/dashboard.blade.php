@extends('layouts.app')

@section('title', 'Dashboard Professore - OpenLab')

@section('styles')
<style>
    .calendar-cell.cell-disabled {
        background-color: rgba(255, 255, 255, 0.02) !important;
        cursor: not-allowed;
        opacity: 0.4;
    }
    .calendar-cell.calendar-today {
        background-color: rgba(99, 102, 241, 0.15) !important;
        border: 2px solid var(--accent) !important;
    }
    .calendar-cell.calendar-today.cell-disabled {
        background-color: rgba(99, 102, 241, 0.08) !important;
        border: 1.5px dashed var(--accent) !important;
        opacity: 0.55;
    }
    th.header-today {
        background-color: rgba(99, 102, 241, 0.2) !important;
        color: #fff !important;
        border-bottom: 3px solid var(--accent) !important;
    }
    .calendar-cell.past-booking {
        background: rgba(255, 255, 255, 0.05) !important;
        border-left: 4px solid #94a3b8 !important; /* Slate gray */
        color: #94a3b8 !important;
        cursor: not-allowed !important;
        opacity: 0.6;
    }
    .calendar-cell.past-booking .booking-title, 
    .calendar-cell.past-booking .booking-other-title, 
    .calendar-cell.past-booking .booking-prof, 
    .calendar-cell.past-booking .booking-time {
        color: #94a3b8 !important;
    }
    .calendar-table.in-maintenance {
        border: 2px solid #eab308 !important;
    }
    .calendar-table.in-maintenance th {
        background-color: rgba(234, 179, 8, 0.15) !important;
    }
    .calendar-table.in-maintenance td.calendar-cell:not(.booked):not(.other-booked) {
        background-color: rgba(234, 179, 8, 0.08) !important;
        cursor: not-allowed !important;
    }
    .maintenance-badge {
        background-color: #fef08a !important;
        color: #854d0e !important;
        border: 1px solid #facc15 !important;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="glass-card p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="text-white mb-1">Area Professore</h1>
                <p class="text-secondary mb-0">Gestisci i tuoi corsi, prenota i laboratori e approva le iscrizioni degli studenti</p>
            </div>
            <div>
                <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#corsoModal">
                    <i class="fa-solid fa-plus me-2"></i>Aggiungi Corso
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="profTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="corsi-tab" data-bs-toggle="tab" data-bs-target="#corsi-pane" type="button" role="tab">
            <i class="fa-solid fa-book me-2"></i>Miei Corsi
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="prenotazioni-tab" data-bs-toggle="tab" data-bs-target="#prenotazioni-pane" type="button" role="tab">
            <i class="fa-solid fa-calendar-week me-2"></i>Calendario Laboratori
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="iscrizioni-tab" data-bs-toggle="tab" data-bs-target="#iscrizioni-pane" type="button" role="tab">
            <i class="fa-solid fa-user-check me-2"></i>Approvazioni 
            <span class="badge bg-danger ms-1 d-none" id="badge-iscrizioni-count">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="gestione-studenti-tab" data-bs-toggle="tab" data-bs-target="#gestione-studenti-pane" type="button" role="tab">
            <i class="fa-solid fa-users-gear me-2"></i>Gestione Studenti
        </button>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="profTabsContent">
    
    <!-- Tab Corsi -->
    <div class="tab-pane fade show active" id="corsi-pane" role="tabpanel">
        <div class="glass-card p-4">
            <h3 class="text-white mb-4 display-font h5">Seleziona un corso per visualizzare il relativo orario</h3>
            <div class="row row-cols-1 row-cols-md-3 g-3" id="corsi-container">
                <!-- Caricamento dinamico dei corsi -->
                <div class="col-12 text-center text-secondary py-4">
                    <span class="spinner-border spinner-border-sm me-2"></span>Caricamento corsi...
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Calendario -->
    <div class="tab-pane fade" id="prenotazioni-pane" role="tabpanel">
        <div class="glass-card p-4">
            <!-- No Course Alert -->
            <div id="no-course-alert" class="text-center py-5">
                <i class="fa-regular fa-calendar fa-4x text-secondary mb-3"></i>
                <h4 class="text-white">Nessun Corso Selezionato</h4>
                <p class="text-secondary">Torna alla scheda "Miei Corsi" e seleziona un corso per sbloccare la prenotazione delle aule.</p>
            </div>

            <!-- Calendario Attivo -->
            <div id="active-calendar-section" class="d-none animate-fade-in">
                <div class="row mb-4 align-items-center justify-content-between gap-3">
                    <div class="col-md-4">
                        <span class="badge bg-accent mb-2">Corso Attivo</span>
                        <h3 class="text-white mb-0 display-font h5" id="calendar-course-title">NOME CORSO</h3>
                        <p class="text-secondary mb-0 small">Tipologia richiesta: <strong id="calendar-course-type">--</strong></p>
                    </div>
                    <div class="col-md-4">
                        <label for="calendar-aula-select" class="form-label text-secondary small fw-bold mb-1 d-flex justify-content-between align-items-center">
                            Visualizza Aula / Laboratorio
                            <span id="calendar-aula-status-badge" class="d-none"></span>
                        </label>
                        <select id="calendar-aula-select" class="form-select bg-dark text-white border-secondary">
                            <option value="" disabled selected hidden>Seleziona un laboratorio...</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end align-items-center">
                        <button class="btn btn-sm btn-outline-custom me-3" id="btn-prev-week">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="fw-bold display-font text-white small" id="current-week-label">
                            <!-- Mostra data lunedì - venerdì -->
                        </span>
                        <button class="btn btn-sm btn-outline-custom ms-3" id="btn-next-week">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="alert alert-info py-2 small" role="alert">
                    <i class="fa-solid fa-circle-info me-2"></i><strong>Come funziona:</strong> Clicca su una cella vuota per prenotare un'aula. Clicca su una tua prenotazione (in verde) per modificarla o cancellarla.
                </div>

                <!-- Tabella Orario -->
                <div class="calendar-container">
                    <table class="calendar-table">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Orario</th>
                                <th id="day-col-1">Lunedì<br><span class="text-secondary small font-monospace">--/--</span></th>
                                <th id="day-col-2">Martedì<br><span class="text-secondary small font-monospace">--/--</span></th>
                                <th id="day-col-3">Mercoledì<br><span class="text-secondary small font-monospace">--/--</span></th>
                                <th id="day-col-4">Giovedì<br><span class="text-secondary small font-monospace">--/--</span></th>
                                <th id="day-col-5">Venerdì<br><span class="text-secondary small font-monospace">--/--</span></th>
                            </tr>
                        </thead>
                        <tbody id="calendar-tbody">
                            <!-- Generato dinamicamente via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Approvazione Studenti -->
    <div class="tab-pane fade" id="iscrizioni-pane" role="tabpanel">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-white mb-0 display-font h5">Richieste Iscrizioni Studenti</h3>
                <button class="btn btn-accent btn-sm d-none" id="btn-approva-tutti">
                    <i class="fa-solid fa-check-double me-1"></i> Approva Tutti
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover border-0 align-middle" id="table-iscrizioni">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--card-border);">
                            <th scope="col" class="bg-transparent text-secondary py-3">Studente</th>
                            <th scope="col" class="bg-transparent text-secondary py-3">Corso</th>
                            <th scope="col" class="bg-transparent text-secondary py-3">Data Richiesta</th>
                            <th scope="col" class="bg-transparent text-secondary py-3 text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="list-iscrizioni-tbody">
                        <!-- Caricamento dinamico tramite AJAX -->
                        <tr>
                            <td colspan="4" class="text-center py-4 text-secondary">
                                Nessuna richiesta in sospeso.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Gestione Studenti -->
    <div class="tab-pane fade" id="gestione-studenti-pane" role="tabpanel">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h3 class="text-white mb-0 display-font h5">Gestione Studenti Approvati / Rifiutati</h3>
                <div style="min-width: 250px;">
                    <select id="filter-corso-studenti" class="form-select bg-dark text-white border-secondary">
                        <option value="all">Tutti i corsi</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover border-0 align-middle" id="table-gestione-studenti">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--card-border);">
                            <th scope="col" class="bg-transparent text-secondary py-3">Studente</th>
                            <th scope="col" class="bg-transparent text-secondary py-3">Corso</th>
                            <th scope="col" class="bg-transparent text-secondary py-3">Stato</th>
                            <th scope="col" class="bg-transparent text-secondary py-3 text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="list-gestione-studenti-tbody">
                        <!-- Caricamento dinamico tramite AJAX -->
                        <tr>
                            <td colspan="4" class="text-center py-4 text-secondary">
                                Nessuno studente gestito.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Aggiungi Corso -->
<div class="modal fade" id="corsoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title display-font">Aggiungi Nuovo Corso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-corso">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="Nome" class="form-label text-secondary small fw-bold">Nome Corso</label>
                        <input type="text" class="form-control" id="Nome" name="Nome" required placeholder="Es. Laboratorio di PLC, Sistemi Elettronici">
                    </div>
                    <div class="mb-3">
                        <label for="Tipologia_Materia" class="form-label text-secondary small fw-bold">Tipologia Materia (Aula richiesta)</label>
                        <input type="text" class="form-control" id="Tipologia_Materia" name="Tipologia_Materia" required placeholder="Es. Elettronica, Informatica, Chimica">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-accent">Aggiungi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuova Prenotazione -->
<div class="modal fade" id="prenotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title display-font">Nuova Prenotazione Aula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-nuova-prenotazione">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Giorno Selezionato</label>
                        <input type="text" class="form-control" id="form-prenota-giorno-label" readonly>
                        <input type="hidden" id="form-prenota-data" name="Data">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="form-prenota-ora-inizio" class="form-label text-secondary small fw-bold">Ora Inizio</label>
                            <input type="time" class="form-control" id="form-prenota-ora-inizio" name="Ora_Inizio" readonly>
                        </div>
                        <div class="col-6">
                            <label for="form-prenota-ora-fine" class="form-label text-secondary small fw-bold">Ora Fine</label>
                            <input type="time" class="form-control" id="form-prenota-ora-fine" name="Ora_Fine" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="form-prenota-durata" class="form-label text-secondary small fw-bold">Durata (Ore)</label>
                        <select class="form-select" id="form-prenota-durata" required>
                            <option value="1">1 Ora</option>
                            <option value="2">2 Ore</option>
                            <option value="3">3 Ore</option>
                            <option value="4">4 Ore</option>
                            <option value="5">5 Ore</option>
                            <option value="6">6 Ore</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Laboratorio Selezionato</label>
                        <input type="text" class="form-control" id="form-prenota-aula-label" readonly>
                        <input type="hidden" id="form-prenota-aula" name="ID_Aula">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-accent">Conferma Prenotazione</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifica/Cancella Prenotazione Professore -->
<div class="modal fade" id="modificaPrenotazioneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title display-font">Gestisci Prenotazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-modifica-prenotazione">
                <div class="modal-body">
                    <input type="hidden" id="modifica-prenotazione-id">
                    
                    <div class="mb-3">
                        <label for="modifica-prenota-aula" class="form-label text-secondary small fw-bold">Laboratorio</label>
                        <select class="form-select" id="modifica-prenota-aula" name="ID_Aula" required>
                            <!-- Caricate dinamicamente -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modifica-prenota-data" class="form-label text-secondary small fw-bold">Data</label>
                        <input type="date" class="form-control" id="modifica-prenota-data" name="Data" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="modifica-prenota-ora-inizio" class="form-label text-secondary small fw-bold">Ora Inizio</label>
                            <select class="form-select" id="modifica-prenota-ora-inizio" name="Ora_Inizio" required>
                                <option value="08:00">08:00</option>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="12:00">12:00</option>
                                <option value="13:00">13:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                                <option value="17:00">17:00</option>
                                <option value="18:00">18:00</option>
                                <option value="19:00">19:00</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="modifica-prenota-ora-fine" class="form-label text-secondary small fw-bold">Ora Fine</label>
                            <select class="form-select" id="modifica-prenota-ora-fine" name="Ora_Fine" required>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="12:00">12:00</option>
                                <option value="13:00">13:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                                <option value="17:00">17:00</option>
                                <option value="18:00">18:00</option>
                                <option value="19:00">19:00</option>
                                <option value="20:00">20:00</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="btn-elimina-prenotazione">
                        <i class="fa-solid fa-trash me-2"></i>Elimina Prenotazione
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-custom me-2" data-bs-dismiss="modal">Chiudi</button>
                        <button type="submit" class="btn btn-accent">Aggiorna</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let activeCorso = null; // Memorizza il corso selezionato
        let corsiList = [];
        let allIscrizioniGestite = [];
        let allPrenotazioni = [];
        let suitableAule = [];
        let selectedWeekStart = getMonday(new Date()); // Lunedì corrente

        // Fasce orarie 1 ora
        const orari = [
            '08:00 - 09:00',
            '09:00 - 10:00',
            '10:00 - 11:00',
            '11:00 - 12:00',
            '12:00 - 13:00',
            '13:00 - 14:00',
            '14:00 - 15:00',
            '15:00 - 16:00',
            '16:00 - 17:00',
            '17:00 - 18:00',
            '18:00 - 19:00',
            '19:00 - 20:00'
        ];

        // Inizializza
        loadCorsi();
        loadIscrizioniPendenti();
        loadIscrizioniGestite();

        // Gestione corsi
        function loadCorsi() {
            $.ajax({
                url: '/professore/corsi',
                method: 'GET',
                success: function(corsi) {
                    corsiList = corsi;
                    renderCorsi(corsi);
                    updateCorsoFilterOptions(corsi);
                },
                error: function() {
                    showToast("Errore nel caricamento dei corsi.", "danger");
                }
            });
        }

        function renderCorsi(corsi) {
            const container = $('#corsi-container');
            container.empty();

            if (corsi.length === 0) {
                container.append(`
                    <div class="col-12 text-center py-5">
                        <i class="fa-solid fa-book-open fa-3x text-secondary mb-3"></i>
                        <h5 class="text-white">Non hai ancora inserito corsi</h5>
                        <p class="text-secondary">Usa il pulsante "Aggiungi Corso" in alto a destra per creare il tuo primo corso.</p>
                    </div>
                `);
                return;
            }

            corsi.forEach(corso => {
                const isActive = activeCorso && activeCorso.ID_Corso == corso.ID_Corso ? 'active' : '';
                const card = `
                    <div class="col animate-fade-in">
                        <div class="course-card ${isActive}" data-id="${corso.ID_Corso}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="h5 text-white mb-0 font-display pe-2" style="font-size: 1.05rem; word-break: break-word;">${corso.Nome}</h4>
                                <button class="btn btn-link btn-sm text-danger p-0 btn-delete-corso" data-id="${corso.ID_Corso}" style="text-decoration: none; margin-top: -2px;">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            <p class="text-secondary small mb-0">Materia: <strong class="text-light">${corso.Tipologia_Materia}</strong></p>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        $('#form-corso').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/professore/corsi',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showToast('Corso aggiunto con successo!');
                    $('#corsoModal').modal('hide');
                    $('#form-corso')[0].reset();
                    loadCorsi();
                },
                error: function(xhr) {
                    let msg = "Errore durante il salvataggio.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        // Selezione Corso
        $(document).on('click', '.course-card', function() {
            const id = $(this).data('id');
            activeCorso = corsiList.find(c => c.ID_Corso == id);
            
            // Aggiorna lo stato visualizzato
            $('.course-card').removeClass('active');
            $(this).addClass('active');

            // Carica aule idonee in base alla tipologia del corso
            loadAuleIdonee(activeCorso.ID_Corso);

            // Attiva la scheda calendario
            $('#no-course-alert').addClass('d-none');
            $('#active-calendar-section').removeClass('d-none');
            
            $('#calendar-course-title').text(activeCorso.Nome);
            $('#calendar-course-type').text(activeCorso.Tipologia_Materia);

            // Carica tutte le prenotazioni del sistema per comporre la griglia (e individuare conflitti)
            loadTutteLePrenotazioni();
            
            // Switch automatico al tab Calendario
            const triggerEl = document.querySelector('#prenotazioni-tab');
            let tab = bootstrap.Tab.getInstance(triggerEl);
            if (!tab) tab = new bootstrap.Tab(triggerEl);
            tab.show();
        });

        // Elimina Corso
        $(document).on('click', '.btn-delete-corso', function(e) {
            e.stopPropagation(); // Evita di selezionare il corso
            
            const id = $(this).data('id');
            const corso = corsiList.find(c => c.ID_Corso == id);
            
            if (confirm(`Sei sicuro di voler eliminare il corso "${corso.Nome}"? Attenzione: questa operazione cancellerà tutte le relative prenotazioni sul calendario e le iscrizioni degli studenti a questo corso.`)) {
                $.ajax({
                    url: `/professore/corsi/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast("Corso eliminato con successo!", "success");
                        
                        // Se il corso eliminato era quello attivo, resettiamo il calendario
                        if (activeCorso && activeCorso.ID_Corso == id) {
                            activeCorso = null;
                            $('#no-course-alert').removeClass('d-none');
                            $('#active-calendar-section').addClass('d-none');
                        }
                        
                        loadCorsi();
                    },
                    error: function(xhr) {
                        let msg = "Errore durante l'eliminazione del corso.";
                        if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast(msg, "danger");
                    }
                });
            }
        });

        function loadAuleIdonee(idCorso) {
            $.ajax({
                url: '/professore/aule-idonee',
                method: 'GET',
                data: { ID_Corso: idCorso },
                success: function(aule) {
                    suitableAule = aule;
                    populateAuleSelects(aule);
                }
            });
        }

        function populateAuleSelects(aule) {
            const prenotaSelect = $('#form-prenota-aula');
            const modificaSelect = $('#modifica-prenota-aula');
            const calendarSelect = $('#calendar-aula-select');
            
            prenotaSelect.empty();
            modificaSelect.empty();
            calendarSelect.empty();
            
            if (aule.length === 0) {
                const optNull = '<option value="">Nessun laboratorio disponibile</option>';
                prenotaSelect.append(optNull);
                modificaSelect.append(optNull);
                calendarSelect.append(optNull);
                return;
            }

            let html = '';
            let calendarHtml = '<option value="" disabled selected hidden>Seleziona un laboratorio...</option>';
            aule.forEach(aula => {
                const opt = `<option value="${aula.ID_Aula}">${aula.Nome_Aula} (Tipologia: ${aula.Tipologia_Aula}, Capienza: ${aula.Capienza})</option>`;
                html += opt;
                calendarHtml += opt;
            });

            prenotaSelect.append(html);
            modificaSelect.append(html);
            calendarSelect.append(calendarHtml);

            // Auto-select first room
            if (aule.length > 0) {
                calendarSelect.val(aule[0].ID_Aula);
            }
        }

        $(document).on('change', '#calendar-aula-select', function() {
            renderCalendarGrid();
        });

        // Calendiario e prenotazioni
        function loadTutteLePrenotazioni() {
            // L'amministratore e i docenti caricano tutte le prenotazioni
            $.ajax({
                url: '/professore/prenotazioni', // Endpoint che ritorna tutte le prenotazioni
                method: 'GET',
                success: function(prenotazioni) {
                    allPrenotazioni = prenotazioni;
                    renderCalendarGrid();
                }
            });
        }

        function toSeconds(timeStr) {
            if (!timeStr) return 0;
            const parts = timeStr.split(':');
            const h = parseInt(parts[0], 10) || 0;
            const m = parseInt(parts[1], 10) || 0;
            const s = parseInt(parts[2], 10) || 0;
            return h * 3600 + m * 60 + s;
        }

        function checkConflict(aulaId, data, oraInizio, oraFine, excludeBookingId = null) {
            const startSec = toSeconds(oraInizio);
            const endSec = toSeconds(oraFine);
            
            const conflict = allPrenotazioni.find(p => {
                if (excludeBookingId && p.ID_Prenotazione == excludeBookingId) return false;
                
                const pStartSec = toSeconds(p.Ora_Inizio);
                const pEndSec = toSeconds(p.Ora_Fine);
                
                return p.ID_Aula == aulaId &&
                       p.Data === data &&
                       pStartSec < endSec &&
                       pEndSec > startSec;
            });
            
            return conflict;
        }

        function renderCalendarGrid() {
            if (!activeCorso) return;

            const selectedAulaId = $('#calendar-aula-select').val();
            const tbody = $('#calendar-tbody');
            tbody.empty();

            if (!selectedAulaId) {
                tbody.html('<tr><td colspan="6" class="text-center text-secondary py-5"><i class="fa-solid fa-circle-info fa-2x mb-3 d-block"></i>Seleziona un laboratorio dal menu a tendina sopra per visualizzare il calendario e prenotare.</td></tr>');
                return;
            }

            const selectedAula = suitableAule.find(a => a.ID_Aula == selectedAulaId);
            const isMaintenance = selectedAula && selectedAula.Stato === 'Manutenzione';
            
            const table = $('.calendar-table');
            const statusBadge = $('#calendar-aula-status-badge');
            
            if (isMaintenance) {
                table.addClass('in-maintenance');
                statusBadge.removeClass('d-none').addClass('maintenance-badge').html('<i class="fa-solid fa-triangle-exclamation"></i> In Manutenzione');
            } else {
                table.removeClass('in-maintenance');
                statusBadge.addClass('d-none').removeClass('maintenance-badge');
            }

            updateCalendarDates();

            const skipCells = {};

            orari.forEach((fascia, index) => {
                let row = `<tr><td class="calendar-time-col">${fascia}</td>`;
                
                // Da Lunedì (1) a Venerdì (5)
                for (let dayOffset = 0; dayOffset < 5; dayOffset++) {
                    if (skipCells[dayOffset + '_' + index]) {
                        continue;
                    }

                    const cellDate = new Date(selectedWeekStart);
                    cellDate.setDate(selectedWeekStart.getDate() + dayOffset);
                    const cellDateStr = formatDateForSQL(cellDate);

                    const hourStartStr = fascia.split(' - ')[0];
                    const hourEndStr = fascia.split(' - ')[1];
                    const hourStartSec = toSeconds(hourStartStr);
                    const hourEndSec = toSeconds(hourEndStr);

                    // Cerca se esiste una prenotazione per QUESTA AULA SPECIFICA
                    const booking = allPrenotazioni.find(p => {
                        const bookingStartSec = toSeconds(p.Ora_Inizio);
                        const bookingEndSec = toSeconds(p.Ora_Fine);
                        return p.ID_Aula == selectedAulaId &&
                               p.Data === cellDateStr &&
                               bookingStartSec <= hourStartSec &&
                               bookingEndSec >= hourEndSec;
                    });

                    if (booking) {
                        const todayStr = formatDateForSQL(new Date());
                        const isPast = booking.Data < todayStr;
                        const isMyBooking = booking.corso.ID_Professore == "{{ Auth::id() }}";
                        let cellClass = isMyBooking ? 'calendar-cell booked' : 'calendar-cell other-booked';
                        if (isPast) {
                            cellClass += ' past-booking';
                        }
                        const titleClass = isMyBooking ? 'booking-title' : 'booking-other-title';
                        
                        // Calcola rowspan
                        const startHour = parseInt(hourStartStr.split(':')[0]);
                        const endHour = parseInt(booking.Ora_Fine.split(':')[0]);
                        const span = Math.max(1, endHour - startHour);
                        
                        for (let r = 1; r < span; r++) {
                            skipCells[dayOffset + '_' + (index + r)] = true;
                        }
                        
                        row += `
                            <td class="${cellClass}" data-id="${booking.ID_Prenotazione}" rowspan="${span}">
                                <div class="booking-info animate-fade-in" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                                    <div class="${titleClass}" title="${booking.corso.Nome}">${booking.corso.Nome}</div>
                                    <div class="booking-prof">${booking.aula.Nome_Aula} (${booking.corso.professore.Username})</div>
                                    <div class="booking-time">${booking.Ora_Inizio.substring(0,5)} - ${booking.Ora_Fine.substring(0,5)}</div>
                                </div>
                            </td>
                        `;
                    } else {
                        const todayStr = formatDateForSQL(new Date());
                        const isPastOrToday = cellDateStr <= todayStr;
                        const isToday = cellDateStr === todayStr;
                        
                        let cellClass = 'calendar-cell';
                        if (isPastOrToday) {
                            cellClass += ' cell-disabled';
                        }
                        if (isToday) {
                            cellClass += ' calendar-today';
                        }

                        row += `
                            <td class="${cellClass}" data-date="${cellDateStr}" data-inizio="${hourStartStr}:00" data-fine="${hourEndStr}:00">
                            </td>
                        `;
                    }
                }
                row += '</tr>';
                tbody.append(row);
            });
        }

        function updateCalendarDates() {
            const days = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì'];
            const todayStr = formatDateForSQL(new Date());
            
            for (let i = 0; i < 5; i++) {
                const date = new Date(selectedWeekStart);
                date.setDate(selectedWeekStart.getDate() + i);
                
                const d = String(date.getDate()).padStart(2, '0');
                const m = String(date.getMonth() + 1).padStart(2, '0');
                
                const dateStr = formatDateForSQL(date);
                const col = $(`#day-col-${i+1}`);
                col.html(`${days[i]}<br><span class="text-secondary small font-monospace">${d}/${m}</span>`);
                
                if (dateStr === todayStr) {
                    col.addClass('header-today');
                } else {
                    col.removeClass('header-today');
                }
            }

            const weekEnd = new Date(selectedWeekStart);
            weekEnd.setDate(selectedWeekStart.getDate() + 4);
            
            const startD = String(selectedWeekStart.getDate()).padStart(2, '0');
            const startM = String(selectedWeekStart.getMonth() + 1).padStart(2, '0');
            const endD = String(weekEnd.getDate()).padStart(2, '0');
            const endM = String(weekEnd.getMonth() + 1).padStart(2, '0');
            
            $('#current-week-label').text(`${startD}/${startM} - ${endD}/${endM}`);
        }

        // Cella click
        $(document).on('click', '.calendar-cell', function() {
            if ($('.calendar-table').hasClass('in-maintenance') && !$(this).hasClass('booked')) {
                showToast("Quest'aula è in manutenzione e non può essere prenotata.", "warning");
                return;
            }

            if ($(this).hasClass('other-booked')) {
                return; // Prenotata da altri professori, non cliccabile
            }
            
            if ($(this).hasClass('cell-disabled')) {
                showToast("Non è possibile prenotare lezioni per oggi o nel passato. La prenotazione deve essere effettuata a partire da domani.", "warning");
                return;
            }
            
            if ($(this).hasClass('booked')) {
                // Modifica/Cancella mia prenotazione
                const id = $(this).data('id');
                const booking = allPrenotazioni.find(p => p.ID_Prenotazione == id);
                
                if (booking) {
                    const todayStr = formatDateForSQL(new Date());
                    if (booking.Data <= todayStr) {
                        showToast("Non è possibile modificare lezioni passate o di oggi.", "warning");
                        return;
                    }

                    $('#modifica-prenotazione-id').val(booking.ID_Prenotazione);
                    $('#modifica-prenota-aula').val(booking.ID_Aula);
                    $('#modifica-prenota-data').val(booking.Data);
                    $('#modifica-prenota-ora-inizio').val(booking.Ora_Inizio.substring(0, 5));
                    $('#modifica-prenota-ora-fine').val(booking.Ora_Fine.substring(0, 5));
                    
                    const modal = new bootstrap.Modal(document.getElementById('modificaPrenotazioneModal'));
                    modal.show();
                }
            } else {
                // Nuova prenotazione
                if (!activeCorso) return;
                
                if (suitableAule.length === 0) {
                    showToast("Impossibile prenotare: nessuna aula disponibile.", "warning");
                    return;
                }

                const selectedAulaId = $('#calendar-aula-select').val();
                if (!selectedAulaId) {
                    showToast("Seleziona prima un laboratorio dal menu a tendina.", "warning");
                    return;
                }

                const data = $(this).data('date');
                const oraInizio = $(this).data('inizio');

                // Label giorno leggibile
                const dateParts = data.split('-');
                const labelGiorno = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;

                $('#form-prenota-giorno-label').val(labelGiorno);
                $('#form-prenota-data').val(data);
                $('#form-prenota-ora-inizio').val(oraInizio.substring(0,5));
                $('#form-prenota-durata').val('1');
                
                $('#form-prenota-aula').val(selectedAulaId);
                const selectedAula = suitableAule.find(a => a.ID_Aula == selectedAulaId);
                if (selectedAula) {
                    $('#form-prenota-aula-label').val(selectedAula.Nome_Aula);
                }
                
                updateOraFine();

                const modal = new bootstrap.Modal(document.getElementById('prenotaModal'));
                modal.show();
            }
        });

        function updateOraFine() {
            const start = $('#form-prenota-ora-inizio').val(); // e.g. "09:00"
            if (!start) return;
            const hours = parseInt(start.split(':')[0]);
            const minutes = start.split(':')[1];
            const duration = parseInt($('#form-prenota-durata').val());
            
            let endHours = hours + duration;
            if (endHours > 20) {
                endHours = 20;
                // adjust duration dropdown value if it exceeds limits
                $('#form-prenota-durata').val(20 - hours);
            }
            
            const endStr = String(endHours).padStart(2, '0') + ':' + minutes;
            $('#form-prenota-ora-fine').val(endStr);
        }

        $('#form-prenota-durata').on('change', updateOraFine);

        // Invio nuova prenotazione
        $('#form-nuova-prenotazione').on('submit', function(e) {
            e.preventDefault();
            
            const aulaId = $('#form-prenota-aula').val();
            if (!aulaId) {
                showToast("Seleziona un'aula valida.", "warning");
                return;
            }

            const data = $('#form-prenota-data').val();
            const oraInizio = $('#form-prenota-ora-inizio').val();
            const oraFine = $('#form-prenota-ora-fine').val();
            
            const conflict = checkConflict(aulaId, data, oraInizio, oraFine);
            if (conflict) {
                showToast("Orario non valido: l'aula selezionata è già occupata in questa fascia oraria.", "danger");
                return;
            }

            $.ajax({
                url: '/professore/prenotazioni',
                method: 'POST',
                data: {
                    Data: $('#form-prenota-data').val(),
                    Ora_Inizio: $('#form-prenota-ora-inizio').val(),
                    Ora_Fine: $('#form-prenota-ora-fine').val(),
                    ID_Aula: aulaId,
                    ID_Corso: activeCorso.ID_Corso
                },
                success: function(response) {
                    showToast('Aula prenotata con successo!');
                    $('#prenotaModal').modal('hide');
                    loadTutteLePrenotazioni();
                },
                error: function(xhr) {
                    let msg = "Errore durante la prenotazione.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        // Invio modifica prenotazione
        $('#form-modifica-prenotazione').on('submit', function(e) {
            e.preventDefault();
            const id = $('#modifica-prenotazione-id').val();
            
            const aulaId = $('#modifica-prenota-aula').val();
            const data = $('#modifica-prenota-data').val();
            const oraInizio = $('#modifica-prenota-ora-inizio').val();
            const oraFine = $('#modifica-prenota-ora-fine').val();

            const conflict = checkConflict(aulaId, data, oraInizio, oraFine, id);
            if (conflict) {
                showToast("Orario non valido: l'aula selezionata è già occupata in questa fascia oraria.", "danger");
                return;
            }
            
            $.ajax({
                url: `/professore/prenotazioni/${id}`,
                method: 'PUT',
                data: {
                    ID_Aula: $('#modifica-prenota-aula').val(),
                    Data: $('#modifica-prenota-data').val(),
                    Ora_Inizio: $('#modifica-prenota-ora-inizio').val(),
                    Ora_Fine: $('#modifica-prenota-ora-fine').val()
                },
                success: function(response) {
                    showToast('Prenotazione aggiornata!');
                    $('#modificaPrenotazioneModal').modal('hide');
                    loadTutteLePrenotazioni();
                },
                error: function(xhr) {
                    let msg = "Errore durante la modifica.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        $('#btn-elimina-prenotazione').on('click', function() {
            if (confirm('Sei sicuro di voler cancellare questa prenotazione?')) {
                const id = $('#modifica-prenotazione-id').val();
                
                $.ajax({
                    url: `/professore/prenotazioni/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        showToast('Prenotazione cancellata.');
                        $('#modificaPrenotazioneModal').modal('hide');
                        loadTutteLePrenotazioni();
                    },
                    error: function() {
                        showToast('Errore durante la cancellazione.', 'danger');
                    }
                });
            }
        });

        // Navigazione settimana
        $('#btn-prev-week').on('click', function() {
            selectedWeekStart.setDate(selectedWeekStart.getDate() - 7);
            renderCalendarGrid();
        });

        $('#btn-next-week').on('click', function() {
            selectedWeekStart.setDate(selectedWeekStart.getDate() + 7);
            renderCalendarGrid();
        });

        // Gestione approvaioni
        function loadIscrizioniPendenti() {
            $.ajax({
                url: '/professore/iscrizioni-pendenti',
                method: 'GET',
                success: function(iscrizioni) {
                    renderIscrizioniTable(iscrizioni);
                    updateIscrizioniBadge(iscrizioni.length);
                }
            });
        }

        function renderIscrizioniTable(iscrizioni) {
            const tbody = $('#list-iscrizioni-tbody');
            tbody.empty();

            if (iscrizioni.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center text-secondary py-4">Nessuna richiesta in sospeso.</td></tr>');
                return;
            }

            iscrizioni.forEach(isc => {
                const row = `
                    <tr style="border-bottom: 1px solid var(--card-border);">
                        <td class="text-white fw-bold py-3">${isc.studente.Username}</td>
                        <td class="text-secondary">${isc.corso.Nome}</td>
                        <td class="text-secondary font-monospace">${formatDateItalian(isc.Data_Richiesta)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-success me-1 btn-approva-studente" data-id="${isc.ID_Iscrizione}">
                                <i class="fa-solid fa-check me-1"></i> Approva
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-rifiuta-studente" data-id="${isc.ID_Iscrizione}">
                                <i class="fa-solid fa-xmark me-1"></i> Rifiuta
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        function updateIscrizioniBadge(count) {
            const badge = $('#badge-iscrizioni-count');
            const btnTutti = $('#btn-approva-tutti');
            if (count > 0) {
                badge.text(count).removeClass('d-none');
                btnTutti.removeClass('d-none');
            } else {
                badge.addClass('d-none');
                btnTutti.addClass('d-none');
            }
        }

        $(document).on('click', '.btn-approva-studente', function() {
            const id = $(this).data('id');
            $.ajax({
                url: `/professore/iscrizioni/${id}/approva`,
                method: 'POST',
                success: function() {
                    showToast('Studente approvato con successo!');
                    loadIscrizioniPendenti();
                    loadIscrizioniGestite();
                }
            });
        });

        $(document).on('click', '.btn-rifiuta-studente', function() {
            const id = $(this).data('id');
            if (confirm('Sei sicuro di voler rifiutare questa iscrizione?')) {
                $.ajax({
                    url: `/professore/iscrizioni/${id}/rifiuta`,
                    method: 'POST',
                    success: function() {
                        showToast('Iscrizione rifiutata.', 'warning');
                        loadIscrizioniPendenti();
                        loadIscrizioniGestite();
                    }
                });
            }
        });

        // Bulk approval click handler
        $('#btn-approva-tutti').on('click', function() {
            if (confirm("Sei sicuro di voler approvare TUTTE le richieste d'iscrizione in sospeso?")) {
                $.ajax({
                    url: '/professore/iscrizioni/approva-tutte',
                    method: 'POST',
                    success: function() {
                        showToast('Tutte le richieste sono state approvate con successo!');
                        loadIscrizioniPendenti();
                        loadIscrizioniGestite();
                    },
                    error: function() {
                        showToast("Errore durante l'approvazione di massa.", "danger");
                    }
                });
            }
        });

        // Gestione studenti (APPROVATI/RIFIUTATI)
        function loadIscrizioniGestite() {
            $.ajax({
                url: '/professore/iscrizioni-gestite',
                method: 'GET',
                success: function(iscrizioni) {
                    allIscrizioniGestite = iscrizioni;
                    applyStudentiFilter();
                }
            });
        }

        function updateCorsoFilterOptions(corsi) {
            const select = $('#filter-corso-studenti');
            select.html('<option value="all">Tutti i corsi</option>');
            corsi.forEach(c => {
                select.append(`<option value="${c.ID_Corso}">${c.Nome}</option>`);
            });
        }

        function applyStudentiFilter() {
            const selectedCorsoId = $('#filter-corso-studenti').val();
            if (selectedCorsoId === 'all') {
                renderGestioneStudentiTable(allIscrizioniGestite);
            } else {
                const filtered = allIscrizioniGestite.filter(isc => isc.ID_Corso == selectedCorsoId || (isc.corso && isc.corso.ID_Corso == selectedCorsoId));
                renderGestioneStudentiTable(filtered);
            }
        }

        $(document).on('change', '#filter-corso-studenti', function() {
            applyStudentiFilter();
        });

        function renderGestioneStudentiTable(iscrizioni) {
            const tbody = $('#list-gestione-studenti-tbody');
            tbody.empty();

            if (iscrizioni.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center text-secondary py-4">Nessuno studente approvato o rifiutato.</td></tr>');
                return;
            }

            iscrizioni.forEach(isc => {
                let badgeHtml = '';
                let actionBtnHtml = '';

                if (isc.Stato === 'Approvato') {
                    badgeHtml = `<span class="badge bg-success">Approvato</span>`;
                    actionBtnHtml = `
                        <button class="btn btn-sm btn-outline-danger btn-rifiuta-studente-gestito" data-id="${isc.ID_Iscrizione}">
                            <i class="fa-solid fa-user-minus me-1"></i> Rifiuta/Rimuovi
                        </button>
                    `;
                } else if (isc.Stato === 'Rifiutato') {
                    badgeHtml = `<span class="badge bg-danger">Rifiutato</span>`;
                    actionBtnHtml = `
                        <button class="btn btn-sm btn-success btn-approva-studente-gestito" data-id="${isc.ID_Iscrizione}">
                            <i class="fa-solid fa-user-plus me-1"></i> Approva/Aggiungi
                        </button>
                    `;
                }

                const row = `
                    <tr style="border-bottom: 1px solid var(--card-border);">
                        <td class="text-white fw-bold py-3">${isc.studente.Username}</td>
                        <td class="text-secondary">${isc.corso.Nome}</td>
                        <td>${badgeHtml}</td>
                        <td class="text-end">
                            ${actionBtnHtml}
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        $(document).on('click', '.btn-approva-studente-gestito', function() {
            const id = $(this).data('id');
            $.ajax({
                url: `/professore/iscrizioni/${id}/approva`,
                method: 'POST',
                success: function() {
                    showToast('Studente approvato con successo!');
                    loadIscrizioniPendenti();
                    loadIscrizioniGestite();
                }
            });
        });

        $(document).on('click', '.btn-rifiuta-studente-gestito', function() {
            const id = $(this).data('id');
            if (confirm('Sei sicuro di voler rifiutare/rimuovere questo studente?')) {
                $.ajax({
                    url: `/professore/iscrizioni/${id}/rifiuta`,
                    method: 'POST',
                    success: function() {
                        showToast('Iscrizione rifiutata.', 'warning');
                        loadIscrizioniPendenti();
                        loadIscrizioniGestite();
                    }
                });
            }
        });

        // Helpers date
        function getMonday(d) {
            d = new Date(d);
            var day = d.getDay(),
                diff = d.getDate() - day + (day == 0 ? -6:1);
            return new Date(d.setDate(diff));
        }

        function formatDateForSQL(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function formatDateItalian(dateStr) {
            const parts = dateStr.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
    });
</script>
@endsection
