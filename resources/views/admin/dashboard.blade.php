@extends('layouts.app')

@section('title', 'Dashboard Amministratore - OpenLab')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="glass-card p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="text-white mb-1">Pannello Amministratore</h1>
                <p class="text-secondary mb-0">Gestione aule, utenti e monitoraggio globale delle prenotazioni</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-accent" id="btn-nuova-aula" data-bs-toggle="modal" data-bs-target="#aulaModal">
                    <i class="fa-solid fa-plus me-2"></i>Crea Aula
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Stats Row -->
<div class="row mb-4 g-3" id="admin-stats-row">
    <div class="col-6 col-lg-3">
        <div class="glass-card p-3 h-100 d-flex align-items-center gap-3">
            <div class="stats-icon p-2 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(16, 185, 129, 0.1); color: var(--success-color); width: 42px; height: 42px;">
                <i class="fa-solid fa-door-open fa-lg"></i>
            </div>
            <div>
                <div class="text-secondary small fw-bold" style="font-size: 0.75rem;">Aule Attive</div>
                <div class="h5 text-white mb-0 fw-bold" id="stat-active-rooms">-- / --</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="glass-card p-3 h-100 d-flex align-items-center gap-3">
            <div class="stats-icon p-2 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(99, 102, 241, 0.1); color: var(--accent-color); width: 42px; height: 42px;">
                <i class="fa-solid fa-calendar-check fa-lg"></i>
            </div>
            <div>
                <div class="text-secondary small fw-bold" style="font-size: 0.75rem;">Lezioni Oggi</div>
                <div class="h5 text-white mb-0 fw-bold" id="stat-bookings-today">--</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="glass-card p-3 h-100 d-flex align-items-center gap-3">
            <div class="stats-icon p-2 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(245, 158, 11, 0.1); color: var(--warning-color); width: 42px; height: 42px;">
                <i class="fa-solid fa-users fa-lg"></i>
            </div>
            <div>
                <div class="text-secondary small fw-bold" style="font-size: 0.75rem;">Utenti Totali</div>
                <div class="h5 text-white mb-0 fw-bold" id="stat-total-users">--</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="glass-card p-3 h-100 d-flex align-items-center gap-3">
            <div class="stats-icon p-2 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4; width: 42px; height: 42px;">
                <i class="fa-solid fa-book fa-lg"></i>
            </div>
            <div>
                <div class="text-secondary small fw-bold" style="font-size: 0.75rem;">Corsi Attivi</div>
                <div class="h5 text-white mb-0 fw-bold" id="stat-active-courses">--</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="aule-tab" data-bs-toggle="tab" data-bs-target="#aule-pane" type="button" role="tab">
            <i class="fa-solid fa-door-open me-2"></i>Gestione Aule
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="utenti-tab" data-bs-toggle="tab" data-bs-target="#utenti-pane" type="button" role="tab">
            <i class="fa-solid fa-users-gear me-2"></i>Gestione Utenti
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="calendario-tab" data-bs-toggle="tab" data-bs-target="#calendario-pane" type="button" role="tab">
            <i class="fa-solid fa-calendar-days me-2"></i>Orario Globale
        </button>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="adminTabsContent">
    
    <!-- Tab Gestione Aule -->
    <div class="tab-pane fade show active" id="aule-pane" role="tabpanel">
        <!-- Filters Bar -->
        <div class="glass-card p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 400px;">
                <div class="search-container w-100">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" class="form-control search-input" id="search-aula" placeholder="Cerca aula per nome o tipologia...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="filter-aula-tipologia" class="text-secondary small fw-bold text-nowrap mb-0" style="font-size: 0.8rem;">Tipologia:</label>
                <select class="form-select form-select-sm" id="filter-aula-tipologia" style="min-width: 160px;">
                    <option value="">Tutte le tipologie</option>
                </select>
            </div>
        </div>

        <!-- Classroom Card Grid -->
        <div class="row g-3" id="list-aule-cards">
            <div class="col-12 text-center py-4 text-secondary">
                <span class="spinner-border spinner-border-sm me-2"></span>Caricamento aule...
            </div>
        </div>
    </div>

    <!-- Tab Gestione Utenti -->
    <div class="tab-pane fade" id="utenti-pane" role="tabpanel">
        <!-- Action/Filters Bar -->
        <div class="glass-card p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 350px;">
                <div class="search-container w-100">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" class="form-control search-input" id="search-utente" placeholder="Cerca utente per username...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <label for="filter-utente-ruolo" class="text-secondary small fw-bold text-nowrap mb-0" style="font-size: 0.8rem;">Ruolo:</label>
                    <select class="form-select form-select-sm" id="filter-utente-ruolo" style="min-width: 140px;">
                        <option value="">Tutti i ruoli</option>
                        <option value="Admin">Admin</option>
                        <option value="Professore">Professore</option>
                        <option value="Studente">Studente</option>
                    </select>
                </div>
                <button class="btn btn-accent btn-sm" id="btn-nuovo-utente" data-bs-toggle="modal" data-bs-target="#utenteModal">
                    <i class="fa-solid fa-user-plus me-1.5"></i>Nuovo Utente
                </button>
            </div>
        </div>

        <!-- User Cards Grid -->
        <div class="row g-3" id="list-utenti-cards">
            <div class="col-12 text-center py-4 text-secondary">
                <span class="spinner-border spinner-border-sm me-2"></span>Caricamento utenti...
            </div>
        </div>
    </div>

    <!-- Tab Calendario Globale -->
    <div class="tab-pane fade" id="calendario-pane" role="tabpanel">
        <div class="glass-card p-4">
            <!-- Controlli Calendario -->
            <div class="row mb-4 align-items-center gap-3">
                <div class="col-md-4">
                    <label for="select-calendario-aula" class="form-label text-secondary small fw-bold">Seleziona Aula</label>
                    <select class="form-select" id="select-calendario-aula">
                        <!-- Popolato dinamicamente -->
                    </select>
                </div>
                <div class="col-md-5 d-flex justify-content-center align-items-center mt-md-4">
                    <button class="btn btn-sm btn-outline-custom me-3" id="btn-prev-week">
                        <i class="fa-solid fa-chevron-left"></i> Settimana Prec.
                    </button>
                    <span class="fw-bold display-font text-white" id="current-week-label">
                        <!-- Mostra data lunedì - venerdì -->
                    </span>
                    <button class="btn btn-sm btn-outline-custom ms-3" id="btn-next-week">
                        Settimana Succ. <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
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
@endsection

@section('modals')
<!-- Modal Aggiungi/Modifica Aula -->
<div class="modal fade" id="aulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title display-font" id="aulaModalTitle">Aggiungi Nuova Aula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-aula">
                <div class="modal-body">
                    <input type="hidden" id="aula-id" name="id">
                    <div class="mb-3">
                        <label for="Nome_Aula" class="form-label text-secondary small fw-bold">Nome Aula</label>
                        <input type="text" class="form-control" id="Nome_Aula" name="Nome_Aula" required placeholder="Es. Aula A1, Lab Elettronica">
                    </div>
                    <div class="mb-3">
                        <label for="Tipologia_Aula" class="form-label text-secondary small fw-bold">Tipologia Aula</label>
                        <input type="text" class="form-control" id="Tipologia_Aula" name="Tipologia_Aula" required placeholder="Es. Elettronica, Informatica, Teoria">
                    </div>
                    <div class="mb-3">
                        <label for="Capienza" class="form-label text-secondary small fw-bold">Capienza (Posti)</label>
                        <input type="number" class="form-control" id="Capienza" name="Capienza" required min="1" placeholder="Es. 30">
                    </div>
                    <div class="mb-3">
                        <label for="Stato" class="form-label text-secondary small fw-bold">Stato Aula</label>
                        <select class="form-select" id="Stato" name="Stato">
                            <option value="Disponibile">Disponibile</option>
                            <option value="Manutenzione">In Manutenzione</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-accent" id="btn-save-aula">Salva Aula</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifica/Cancella Prenotazione -->
<div class="modal fade" id="prenotazioneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title display-font">Dettagli Prenotazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-prenotazione">
                <div class="modal-body">
                    <input type="hidden" id="prenotazione-id">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Corso</label>
                        <input type="text" class="form-control" id="prenotazione-corso" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Professore</label>
                        <input type="text" class="form-control" id="prenotazione-prof" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="prenotazione-aula" class="form-label text-secondary small fw-bold">Aula</label>
                        <select class="form-select" id="prenotazione-aula" required>
                            <!-- Popolata dinamicamente con tutte le aule -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="prenotazione-data" class="form-label text-secondary small fw-bold">Data Lezione</label>
                        <input type="date" class="form-control" id="prenotazione-data" required>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="prenotazione-ora-inizio" class="form-label text-secondary small fw-bold">Ora Inizio</label>
                            <select class="form-select" id="prenotazione-ora-inizio" required>
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
                            <label for="prenotazione-ora-fine" class="form-label text-secondary small fw-bold">Ora Fine</label>
                            <select class="form-select" id="prenotazione-ora-fine" required>
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
                    <button type="button" class="btn btn-danger" id="btn-cancella-prenotazione">
                        <i class="fa-solid fa-trash me-2"></i>Cancella
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-custom me-2" data-bs-dismiss="modal">Chiudi</button>
                        <button type="submit" class="btn btn-accent">Applica Modifiche</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuovo/Modifica Utente -->
<div class="modal fade" id="utenteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title display-font" id="utenteModalTitle">Crea Nuovo Utente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-utente">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="utente-username" class="form-label text-secondary small fw-bold">Username</label>
                        <input type="text" class="form-control" id="utente-username" name="Username" required placeholder="Es. mario.rossi">
                    </div>
                    <div class="mb-3">
                        <label for="utente-password" class="form-label text-secondary small fw-bold">Password (min. 6 caratteri)</label>
                        <input type="password" class="form-control" id="utente-password" name="Password" required minlength="6" placeholder="Es. password123">
                    </div>
                    <div class="mb-3">
                        <label for="utente-ruolo" class="form-label text-secondary small fw-bold">Ruolo</label>
                        <select class="form-select" id="utente-ruolo" name="Ruolo" required>
                            <option value="Studente">Studente</option>
                            <option value="Professore">Professore</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="utente-richiede-cambio" name="Richiede_Cambio_Password" value="1" checked>
                        <label class="form-check-label text-secondary small fw-bold text-nowrap" for="utente-richiede-cambio">Forza cambio password al primo accesso</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-accent" id="btn-save-utente">Crea Utente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reimposta Password Utente -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title display-font">Reimposta Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-reset-password">
                <div class="modal-body">
                    <input type="hidden" id="reset-user-id">
                    <div class="mb-2">
                        <span class="text-secondary small">Reimposta la password per l'utente:</span>
                        <strong class="text-white d-block mt-1 mb-3" id="reset-username-label">username</strong>
                    </div>
                    <div class="mb-3">
                        <label for="reset-new-password" class="form-label text-secondary small fw-bold">Nuova Password Temporanea</label>
                        <input type="password" class="form-control" id="reset-new-password" required minlength="6" placeholder="Minimo 6 caratteri">
                    </div>
                    <div class="alert alert-info py-2 px-3 small border-0 mb-0">
                        <i class="fa-solid fa-circle-info me-2 text-accent"></i>Nota: All'utente verrà forzato l'obbligo di cambiare questa password al prossimo accesso.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-accent">Reimposta Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let auleList = [];
        let prenotazioniList = [];
        let utentiList = [];
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

        // Inizializza i dati
        loadStats();
        loadAule();
        loadPrenotazioni();
        loadUtenti();

        // Caricamento statistiche
        function loadStats() {
            $.ajax({
                url: '/admin/stats',
                method: 'GET',
                success: function(stats) {
                    $('#stat-active-rooms').text(`${stats.aule_attive} / ${stats.total_aule}`);
                    $('#stat-bookings-today').text(stats.prenotazioni_oggi);
                    $('#stat-total-users').text(stats.utenti_totali);
                    $('#stat-active-courses').text(stats.corsi_attivi);
                },
                error: function() {
                    console.error("Errore nel caricamento delle statistiche.");
                }
            });
        }

        // Caricamento aule
        function loadAule() {
            $.ajax({
                url: '/admin/aule',
                method: 'GET',
                success: function(aule) {
                    auleList = aule;
                    populateTipologieFilter(aule);
                    renderAuleCards(aule);
                    populateAuleSelects(aule);
                    
                    // Renderizza il calendario una volta caricate le aule
                    renderCalendarGrid();
                },
                error: function() {
                    showToast("Errore nel caricamento delle aule", "danger");
                }
            });
        }

        function populateTipologieFilter(aule) {
            const select = $('#filter-aula-tipologia');
            const currentVal = select.val();
            const tipologie = [...new Set(aule.map(a => a.Tipologia_Aula))];
            
            select.find('option:not(:first)').remove();
            tipologie.forEach(t => {
                select.append(`<option value="${t}">${t}</option>`);
            });

            if (currentVal && tipologie.includes(currentVal)) {
                select.val(currentVal);
            }
        }

        function renderAuleCards(aule) {
            const container = $('#list-aule-cards');
            container.empty();

            const searchVal = $('#search-aula').val().toLowerCase().trim();
            const tipologiaVal = $('#filter-aula-tipologia').val();

            // Filtra le aule in base alla ricerca ed alla tipologia
            const filtered = aule.filter(aula => {
                const matchesSearch = !searchVal || 
                    aula.Nome_Aula.toLowerCase().includes(searchVal) || 
                    aula.Tipologia_Aula.toLowerCase().includes(searchVal);
                const matchesTipologia = !tipologiaVal || aula.Tipologia_Aula === tipologiaVal;
                return matchesSearch && matchesTipologia;
            });

            if (filtered.length === 0) {
                container.append(`
                    <div class="col-12 text-center text-secondary py-5">
                        <i class="fa-solid fa-magnifying-glass fa-2x mb-3 text-muted"></i>
                        <p class="mb-0">Nessuna aula corrisponde ai criteri selezionati.</p>
                    </div>
                `);
                return;
            }

            filtered.forEach(aula => {
                const isDisponibile = aula.Stato === 'Disponibile';
                const statusStyle = isDisponibile 
                    ? 'background: rgba(16, 185, 129, 0.1); color: var(--success-color); border: 1px solid rgba(16, 185, 129, 0.2); cursor: pointer;' 
                    : 'background: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.2); cursor: pointer;';
                
                const card = `
                    <div class="col-md-6 col-lg-4 animate-fade-in">
                        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between position-relative">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h4 class="text-white mb-0 h5">${aula.Nome_Aula}</h4>
                                    <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--text-secondary); border: 1px solid var(--card-border);">${aula.Tipologia_Aula}</span>
                                </div>
                                <div class="text-secondary small mb-3">
                                    <i class="fa-solid fa-users me-2 text-muted"></i>Capienza: <strong>${aula.Capienza}</strong> posti
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
                                <!-- Quick Status Toggle Pill -->
                                <span class="badge py-1.5 px-2.5 rounded-pill btn-toggle-stato-aula" data-id="${aula.ID_Aula}" style="${statusStyle}">
                                    <i class="fa-solid ${isDisponibile ? 'fa-circle-check' : 'fa-circle-exclamation'} me-1.5"></i>${aula.Stato}
                                </span>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-custom edit-aula-btn py-1 px-2" data-id="${aula.ID_Aula}" title="Modifica aula">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-aula py-1 px-2" data-id="${aula.ID_Aula}" title="Elimina aula">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        // Gestori eventi filtri aule
        $('#search-aula').on('input', function() {
            renderAuleCards(auleList);
        });

        $('#filter-aula-tipologia').on('change', function() {
            renderAuleCards(auleList);
        });

        function populateAuleSelects(aule) {
            const calSelect = $('#select-calendario-aula');
            const modalSelect = $('#prenotazione-aula');
            
            const prevCalVal = calSelect.val();
            
            calSelect.empty();
            modalSelect.empty();

            aule.forEach(aula => {
                calSelect.append(`<option value="${aula.ID_Aula}">${aula.Nome_Aula} (${aula.Tipologia_Aula})</option>`);
                modalSelect.append(`<option value="${aula.ID_Aula}">${aula.Nome_Aula}</option>`);
            });

            if (prevCalVal) {
                calSelect.val(prevCalVal);
            }
        }

        // Toggle stato aula con click
        $(document).on('click', '.btn-toggle-stato-aula', function() {
            const id = $(this).data('id');
            $.ajax({
                url: `/admin/aule/${id}/toggle-stato`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast("Stato dell'aula aggiornato con successo!");
                    loadAule();
                    loadStats();
                },
                error: function() {
                    showToast("Errore durante l'aggiornamento dello stato.", "danger");
                }
            });
        });

        // Elimina aula con click
        $(document).on('click', '.btn-delete-aula', function() {
            const id = $(this).data('id');
            const room = auleList.find(a => a.ID_Aula == id);
            
            if (confirm(`Sei sicuro di voler eliminare l'aula "${room.Nome_Aula}"? Attenzione: verranno rimosse anche tutte le relative prenotazioni in orario e gli studenti registrati verranno notificati.`)) {
                $.ajax({
                    url: `/admin/aule/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast("Aula rimossa con successo!");
                        loadAule();
                        loadStats();
                        loadPrenotazioni(); // Ricarica prenotazioni per l'orario
                    },
                    error: function() {
                        showToast("Errore durante l'eliminazione dell'aula.", "danger");
                    }
                });
            }
        });

        // Aula (CREAZIONE/MODIFICA)
        $('#btn-nuova-aula').on('click', function() {
            $('#aulaModalTitle').text('Aggiungi Nuova Aula');
            $('#form-aula')[0].reset();
            $('#aula-id').val('');
        });

        $(document).on('click', '.edit-aula-btn', function() {
            const id = $(this).data('id');
            const aula = auleList.find(a => a.ID_Aula == id);
            
            if (aula) {
                $('#aulaModalTitle').text('Modifica Aula');
                $('#aula-id').val(aula.ID_Aula);
                $('#Nome_Aula').val(aula.Nome_Aula);
                $('#Tipologia_Aula').val(aula.Tipologia_Aula);
                $('#Capienza').val(aula.Capienza);
                $('#Stato').val(aula.Stato);
                
                const myModal = new bootstrap.Modal(document.getElementById('aulaModal'));
                myModal.show();
            }
        });

        $('#form-aula').on('submit', function(e) {
            e.preventDefault();
            const id = $('#aula-id').val();
            const isEdit = id !== '';
            
            const url = isEdit ? `/admin/aule/${id}` : '/admin/aule';
            const method = isEdit ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    showToast(isEdit ? 'Aula modificata con successo!' : 'Aula creata con successo!');
                    $('#aulaModal').modal('hide');
                    loadAule();
                    loadStats();
                },
                error: function(xhr) {
                    let msg = "Errore durante il salvataggio.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        // Gestione utenti
        function loadUtenti() {
            $.ajax({
                url: '/admin/utenti',
                method: 'GET',
                success: function(utenti) {
                    utentiList = utenti;
                    renderUtentiCards(utenti);
                },
                error: function() {
                    showToast("Errore nel caricamento degli utenti", "danger");
                }
            });
        }

        function renderUtentiCards(utenti) {
            const container = $('#list-utenti-cards');
            container.empty();

            const searchVal = $('#search-utente').val().toLowerCase().trim();
            const ruoloVal = $('#filter-utente-ruolo').val();

            const filtered = utenti.filter(u => {
                const matchesSearch = !searchVal || u.Username.toLowerCase().includes(searchVal);
                const matchesRuolo = !ruoloVal || u.Ruolo === ruoloVal;
                return matchesSearch && matchesRuolo;
            });

            if (filtered.length === 0) {
                container.append(`
                    <div class="col-12 text-center text-secondary py-5">
                        <i class="fa-solid fa-users-slash fa-2x mb-3 text-muted"></i>
                        <p class="mb-0">Nessun utente corrisponde ai criteri selezionati.</p>
                    </div>
                `);
                return;
            }

            filtered.forEach(u => {
                let roleBadge = '';
                if (u.Ruolo === 'Admin') {
                    roleBadge = '<span class="badge border border-danger bg-danger-subtle text-danger" style="background: rgba(239, 68, 68, 0.1) !important;">Admin</span>';
                } else if (u.Ruolo === 'Professore') {
                    roleBadge = '<span class="badge border border-accent bg-accent-subtle text-accent" style="background: rgba(99, 102, 241, 0.1) !important;">Professore</span>';
                } else {
                    roleBadge = '<span class="badge border border-success bg-success-subtle text-success" style="background: rgba(16, 185, 129, 0.1) !important;">Studente</span>';
                }

                const isSelf = u.is_self;

                const resetBadge = u.Richiede_Cambio_Password 
                    ? `<span class="badge border border-warning bg-warning-subtle text-warning ms-2 btn-toggle-reset" data-id="${u.ID_Utente}" ${isSelf ? 'disabled style="background: rgba(245, 158, 11, 0.08) !important; color: #a1a1aa; opacity: 0.6;"' : 'title="Clicca per rimuovere l\'obbligo" style="background: rgba(245, 158, 11, 0.1) !important; cursor: pointer;"'}>
                            <i class="fa-solid fa-key me-1"></i>Reset Obbligatorio
                       </span>`
                    : `<span class="badge border border-secondary text-secondary ms-2 btn-toggle-reset" data-id="${u.ID_Utente}" ${isSelf ? 'disabled style="background: rgba(255,255,255,0.02) !important; color: #a1a1aa; opacity: 0.6;"' : 'title="Clicca per forzare il cambio al prossimo login" style="background: rgba(255,255,255,0.03) !important; cursor: pointer;"'}>
                            <i class="fa-solid fa-key me-1"></i>Cambio non richiesto
                       </span>`;

                const card = `
                    <div class="col-md-6 col-lg-4 animate-fade-in">
                        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 70%;">
                                        <i class="fa-regular fa-circle-user text-muted fa-lg"></i>
                                        <h5 class="text-white mb-0 h6 text-truncate" title="${u.Username}">${u.Username}</h5>
                                        ${isSelf ? '<span class="badge py-0.5 px-1.5" style="background: rgba(99, 102, 241, 0.25) !important; border: 1px solid rgba(99, 102, 241, 0.4) !important; color: #e0e7ff !important; font-size: 0.65rem !important;">Tu</span>' : ''}
                                    </div>
                                    ${roleBadge}
                                </div>
                                <div class="text-secondary small d-flex align-items-center flex-wrap gap-1">
                                    <span>Password:</span> ${resetBadge}
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
                                <button class="btn btn-xs btn-outline-custom btn-reimposta-password py-1 px-2.5" data-id="${u.ID_Utente}" data-username="${u.Username}" title="Reimposta password temporanea" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-key me-1 text-warning"></i>Reimposta
                                </button>
                                <button class="btn btn-xs btn-outline-danger btn-delete-utente py-1 px-2.5" data-id="${u.ID_Utente}" ${isSelf ? 'disabled title="Non puoi eliminare te stesso"' : 'title="Elimina utente"'} style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-trash me-1"></i>Elimina
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        // Filtri ricerca e ruolo utenti
        $('#search-utente').on('input', function() {
            renderUtentiCards(utentiList);
        });

        $('#filter-utente-ruolo').on('change', function() {
            renderUtentiCards(utentiList);
        });

        // Toggle reset password
        $(document).on('click', '.btn-toggle-reset', function() {
            const id = $(this).data('id');
            const user = utentiList.find(u => u.ID_Utente == id);
            if (user && user.is_self) return; // Disabilita per se stessi

            $.ajax({
                url: `/admin/utenti/${id}/toggle-reset`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast("Impostazione reset password aggiornata.");
                    loadUtenti();
                },
                error: function(xhr) {
                    let msg = "Errore durante l'aggiornamento.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        // Crea utente submit
        $('#form-utente').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/admin/utenti',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $(this).serialize(),
                success: function(response) {
                    showToast("Utente creato con successo!");
                    $('#utenteModal').modal('hide');
                    $('#form-utente')[0].reset();
                    loadUtenti();
                    loadStats();
                },
                error: function(xhr) {
                    let msg = "Errore durante la creazione dell'utente.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        // Click reimposta password
        $(document).on('click', '.btn-reimposta-password', function() {
            const id = $(this).data('id');
            const username = $(this).data('username');
            
            $('#reset-user-id').val(id);
            $('#reset-username-label').text(username);
            $('#reset-new-password').val('');
            
            const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            modal.show();
        });

        // Submit reimposta password
        $('#form-reset-password').on('submit', function(e) {
            e.preventDefault();
            const id = $('#reset-user-id').val();
            const password = $('#reset-new-password').val();

            $.ajax({
                url: `/admin/utenti/${id}/reset-password`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    Password: password
                },
                success: function(response) {
                    showToast(response.message || 'Password reimpostata con successo!');
                    $('#resetPasswordModal').modal('hide');
                    loadUtenti();
                },
                error: function(xhr) {
                    let msg = "Errore durante il reset.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        // Elimina utente
        $(document).on('click', '.btn-delete-utente', function() {
            const id = $(this).data('id');
            const user = utentiList.find(u => u.ID_Utente == id);
            
            if (user.is_self) return;

            if (confirm(`Sei sicuro di voler eliminare l'utente "${user.Username}"? Attenzione: l'operazione cancellerà in cascata corsi, prenotazioni e notifiche correlate!`)) {
                $.ajax({
                    url: `/admin/utenti/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast("Utente eliminato con successo!");
                        loadUtenti();
                        loadStats();
                        loadPrenotazioni(); // Ricarica l'orario in caso fossero rimosse prenotazioni
                    },
                    error: function(xhr) {
                        let msg = "Errore durante l'eliminazione.";
                        if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast(msg, "danger");
                    }
                });
            }
        });

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
            
            const conflict = prenotazioniList.find(p => {
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

        // Calendario prenotazione
        function loadPrenotazioni() {
            $.ajax({
                url: '/admin/prenotazioni',
                method: 'GET',
                success: function(prenotazioni) {
                    prenotazioniList = prenotazioni;
                    renderCalendarGrid();
                },
                error: function() {
                    showToast("Errore nel caricamento delle prenotazioni", "danger");
                }
            });
        }

        function renderCalendarGrid() {
            const selectedAulaId = $('#select-calendario-aula').val();
            if (!selectedAulaId) return;

            // Aggiorna le etichette delle colonne con le date della settimana selezionata
            updateCalendarDates();

            const tbody = $('#calendar-tbody');
            tbody.empty();

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

                    const hourStart = fascia.split(' - ')[0] + ':00';
                    const hourEnd = fascia.split(' - ')[1] + ':00';

                    // Cerca prenotazione per questa aula, questa data e questo orario
                    const booking = prenotazioniList.find(p => {
                        return p.ID_Aula == selectedAulaId &&
                               p.Data === cellDateStr &&
                               p.Ora_Inizio <= hourStart &&
                               p.Ora_Fine >= hourEnd;
                    });

                    if (booking) {
                        // Calcola rowspan
                        const startHour = parseInt(hourStart.split(':')[0]);
                        const endHour = parseInt(booking.Ora_Fine.split(':')[0]);
                        const span = Math.max(1, endHour - startHour);
                        
                        for (let r = 1; r < span; r++) {
                            skipCells[dayOffset + '_' + (index + r)] = true;
                        }
                        
                        row += `
                            <td class="calendar-cell booked" data-id="${booking.ID_Prenotazione}" rowspan="${span}">
                                <div class="booking-info animate-fade-in" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                                    <div class="booking-title" title="${booking.corso.Nome}">${booking.corso.Nome}</div>
                                    <div class="booking-prof">${booking.corso.professore.Username}</div>
                                    <div class="booking-time">${booking.Ora_Inizio.substring(0,5)} - ${booking.Ora_Fine.substring(0,5)}</div>
                                </div>
                            </td>
                        `;
                    } else {
                        row += `<td class="calendar-cell"></td>`;
                    }
                }
                row += '</tr>';
                tbody.append(row);
            });
        }

        function updateCalendarDates() {
            const days = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì'];
            
            for (let i = 0; i < 5; i++) {
                const date = new Date(selectedWeekStart);
                date.setDate(selectedWeekStart.getDate() + i);
                
                const d = String(date.getDate()).padStart(2, '0');
                const m = String(date.getMonth() + 1).padStart(2, '0');
                
                $(`#day-col-${i+1}`).html(`${days[i]}<br><span class="text-secondary small font-monospace">${d}/${m}</span>`);
            }

            const weekEnd = new Date(selectedWeekStart);
            weekEnd.setDate(selectedWeekStart.getDate() + 4);
            
            const startD = String(selectedWeekStart.getDate()).padStart(2, '0');
            const startM = String(selectedWeekStart.getMonth() + 1).padStart(2, '0');
            const endD = String(weekEnd.getDate()).padStart(2, '0');
            const endM = String(weekEnd.getMonth() + 1).padStart(2, '0');
            
            $('#current-week-label').text(`${startD}/${startM} - ${endD}/${endM}`);
        }

        // Click su cella prenotata
        $(document).on('click', '.calendar-cell.booked', function() {
            const id = $(this).data('id');
            const booking = prenotazioniList.find(p => p.ID_Prenotazione == id);
            
            if (booking) {
                $('#prenotazione-id').val(booking.ID_Prenotazione);
                $('#prenotazione-corso').val(booking.corso.Nome);
                $('#prenotazione-prof').val(booking.corso.professore.Username);
                $('#prenotazione-aula').val(booking.ID_Aula);
                $('#prenotazione-data').val(booking.Data);
                $('#prenotazione-ora-inizio').val(booking.Ora_Inizio.substring(0, 5));
                $('#prenotazione-ora-fine').val(booking.Ora_Fine.substring(0, 5));
                
                const modal = new bootstrap.Modal(document.getElementById('prenotazioneModal'));
                modal.show();
            }
        });

        // Prenotazione (MODIFICA / CANCELLAZIONE)
        $('#form-prenotazione').on('submit', function(e) {
            e.preventDefault();
            const id = $('#prenotazione-id').val();
            
            const oraInizio = $('#prenotazione-ora-inizio').val();
            const oraFine = $('#prenotazione-ora-fine').val();

            const startParts = oraInizio.split(':');
            const endParts = oraFine.split(':');

            const startHour = parseInt(startParts[0], 10);
            const startMin = parseInt(startParts[1], 10);
            const endHour = parseInt(endParts[0], 10);
            const endMin = parseInt(endParts[1], 10);

            if (startMin !== 0 || endMin !== 0) {
                showToast("Le prenotazioni devono iniziare e terminare all'ora esatta (es. 08:00, 09:00).", "warning");
                return;
            }

            if (startHour < 8 || endHour > 20 || (endHour === 20 && endMin !== 0)) {
                showToast("Le prenotazioni sono consentite solo tra le 08:00 e le 20:00.", "warning");
                return;
            }

            if (startHour >= endHour) {
                showToast("L'ora di inizio deve essere precedente all'ora di fine.", "warning");
                return;
            }

            const conflict = checkConflict($('#prenotazione-aula').val(), $('#prenotazione-data').val(), oraInizio, oraFine, id);
            if (conflict) {
                showToast("Orario non valido: l'aula selezionata è già occupata in questa fascia oraria.", "danger");
                return;
            }
            
            $.ajax({
                url: `/admin/prenotazioni/${id}`,
                method: 'PUT',
                data: {
                    ID_Aula: $('#prenotazione-aula').val(),
                    Data: $('#prenotazione-data').val(),
                    Ora_Inizio: oraInizio,
                    Ora_Fine: oraFine
                },
                success: function(response) {
                    showToast('Prenotazione modificata con successo!');
                    $('#prenotazioneModal').modal('hide');
                    loadPrenotazioni();
                },
                error: function(xhr) {
                    let msg = "Errore durante la modifica.";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast(msg, "danger");
                }
            });
        });

        $('#btn-cancella-prenotazione').on('click', function() {
            if (confirm('Sei sicuro di voler cancellare questa prenotazione?')) {
                const id = $('#prenotazione-id').val();
                
                $.ajax({
                    url: `/admin/prenotazioni/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        showToast('Prenotazione cancellata.');
                        $('#prenotazioneModal').modal('hide');
                        loadPrenotazioni();
                        loadStats();
                    },
                    error: function() {
                        showToast('Errore durante la cancellazione.', 'danger');
                    }
                });
            }
        });

        // Navigazione calendario
        $('#select-calendario-aula').on('change', function() {
            renderCalendarGrid();
        });

        $('#btn-prev-week').on('click', function() {
            selectedWeekStart.setDate(selectedWeekStart.getDate() - 7);
            renderCalendarGrid();
        });

        $('#btn-next-week').on('click', function() {
            selectedWeekStart.setDate(selectedWeekStart.getDate() + 7);
            renderCalendarGrid();
        });

        
        function getMonday(d) {
            d = new Date(d);
            var day = d.getDay(),
                diff = d.getDate() - day + (day == 0 ? -6:1); // adjust when day is sunday
            return new Date(d.setDate(diff));
        }

        function formatDateForSQL(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }
    });
</script>
@endsection
