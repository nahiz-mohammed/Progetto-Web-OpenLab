<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestione Aule & Prenotazioni')</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for Premium Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Minimal CSS Theme -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>
    @yield('styles')
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="{{ Auth::check() ? '/' . strtolower(Auth::user()->Ruolo) : '/' }}"><i class="fa-solid fa-graduation-cap me-2"></i>OpenLab</a>
            <button class="btn btn-sm btn-outline-custom border-0 py-1.5 px-2.5" id="theme-toggle" title="Cambia Tema" style="background: transparent !important; color: var(--text-primary) !important;">
                <i class="fa-solid fa-sun text-warning" id="theme-icon"></i>
            </button>
        </div>
    </nav>

    <!-- Content Area -->
    <main class="container my-5 flex-grow-1 animate-fade-in">
        @auth
        <div class="row g-4">
            <!-- Main Content Area -->
            <div class="col-lg-9 col-md-8">
                @yield('content')
            </div>
            
            <!-- Sidebar Area (Right) -->
            <div class="col-lg-3 col-md-4">
                <div class="glass-card p-4 sticky-top" style="top: 100px; z-index: 100;">
                    <div class="text-center mb-4 pb-3 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
                        <div class="mb-2">
                            <i class="fa-regular fa-circle-user fa-3x text-accent"></i>
                        </div>
                        <h5 class="text-white mb-1 fw-bold">{{ Auth::user()->Username }}</h5>
                        <span class="badge bg-secondary">{{ Auth::user()->Ruolo }}</span>
                    </div>
                    
                    <div class="d-flex flex-column gap-3">
                        @if(!Auth::user()->Richiede_Cambio_Password)
                        <!-- Notifications Button -->
                        <button class="btn btn-outline-custom w-100 text-start py-2.5 position-relative" data-bs-toggle="modal" data-bs-target="#notificheModal" id="btn-notifiche" title="Messaggi e Notifiche">
                            <i class="fa-regular fa-bell me-2 text-accent"></i>Messaggi
                            <span class="badge bg-danger ms-auto float-end d-none" id="badge-notifiche-unread" style="margin-top: 2px;">0</span>
                        </button>
                        @endif
                        
                        @if(!Auth::user()->Richiede_Cambio_Password)
                        <!-- Change Password Button -->
                        <button class="btn btn-outline-custom w-100 text-start py-2.5" data-bs-toggle="modal" data-bs-target="#cambiaPasswordModal" title="Cambia Password">
                            <i class="fa-solid fa-key me-2 text-warning"></i>Cambia Password
                        </button>
                        @endif
                        
                        <!-- Logout Form -->
                        <form action="{{ route('logout') }}" method="POST" id="logout-form" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100 text-start py-2.5">
                                <i class="fa-solid fa-power-off me-2"></i>Esci Sessione
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row justify-content-center">
            <div class="col-12">
                @yield('content')
            </div>
        </div>
        @endauth
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center mt-auto" style="background: var(--card-bg); border-top: 1px solid var(--card-border);">
        <div class="container">
            <p class="text-secondary mb-0 small">&copy; {{ date('Y') }} OpenLab - Sistema Gestione Aule Didattiche. Esame di Tecnologie Web.</p>
        </div>
    </footer>

    <!-- Toast Notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="liveToast" class="toast align-items-center text-white border-0 glass-card" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toast-message"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @yield('modals')

    @auth
    <!-- MODAL CAMBIA PASSWORD -->
    <div class="modal fade" id="cambiaPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-cambia-password">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="fa-solid fa-key me-2 text-warning"></i>Cambia Password</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_password_modal" class="form-label text-secondary small">Nuova Password (min. 6 caratteri)</label>
                            <input type="password" class="form-control" id="new_password_modal" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="new_password_modal_confirmation" class="form-label text-secondary small">Conferma Nuova Password</label>
                            <input type="password" class="form-control" id="new_password_modal_confirmation" required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-custom btn-sm" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-accent btn-sm" id="btn-submit-change-password">Salva Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL NOTIFICHE E MESSAGGI -->
    <div class="modal fade" id="notificheModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 600px !important;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title text-white mb-0"><i class="fa-solid fa-bell me-2 text-accent"></i>Messaggi e Notifiche</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-xs btn-outline-custom py-1.5 px-2.5 small" style="font-size: 0.75rem;" id="btn-segna-tutte-lette">
                            <i class="fa-solid fa-check-double me-1"></i>Segna tutte come lette
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <!-- Tabs: Read vs Write -->
                    <ul class="nav nav-tabs px-3 pt-2" id="notificheTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-2" id="ricevute-tab" data-bs-toggle="tab" data-bs-target="#ricevute-pane" type="button" role="tab">
                                <i class="fa-solid fa-inbox me-2"></i>Messaggi Ricevuti
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2" id="invia-tab" data-bs-toggle="tab" data-bs-target="#invia-pane" type="button" role="tab">
                                <i class="fa-regular fa-paper-plane me-2"></i>Invia Messaggio
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Pane list received -->
                        <div class="tab-pane fade show active p-3" id="ricevute-pane" role="tabpanel" style="max-height: 380px; overflow-y: auto;">
                            <div class="d-flex flex-column gap-3" id="lista-notifiche">
                                <!-- Dynamic notifications list -->
                                <div class="text-center text-secondary py-4">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Caricamento notifiche...
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pane send message -->
                        <div class="tab-pane fade p-3" id="invia-pane" role="tabpanel">
                            <form id="form-invia-notifica">
                                <div class="mb-3">
                                    <label for="notifica-ruolo-select" class="form-label text-secondary small">Seleziona Ruolo Destinatario</label>
                                    <select class="form-select" id="notifica-ruolo-select" required>
                                        <option value="">Seleziona ruolo...</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Professore">Professore</option>
                                        <option value="Studente">Studente</option>
                                    </select>
                                </div>
                                <div class="mb-3 d-none" id="notifica-utente-container">
                                    <label for="notifica-utente-select" class="form-label text-secondary small">Seleziona Utente Destinatario</label>
                                    <select class="form-select" id="notifica-utente-select" required>
                                        <option value="">Caricamento utenti...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="notifica-titolo" class="form-label text-secondary small">Oggetto / Titolo</label>
                                    <input type="text" class="form-control" id="notifica-titolo" placeholder="es. Comunicazione importante" required>
                                </div>
                                <div class="mb-3">
                                    <label for="notifica-messaggio" class="form-label text-secondary small">Messaggio</label>
                                    <textarea class="form-control" id="notifica-messaggio" rows="3" placeholder="Inserisci il testo dell'avviso da inviare all'utente selezionato..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary small d-block">Tipo di Avviso</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="notifica-tipo" id="tipo-info" value="info" checked>
                                            <label class="form-check-label text-info" for="tipo-info"><i class="fa-solid fa-circle-info me-1"></i>Info (Azzurro)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="notifica-tipo" id="tipo-warning" value="warning">
                                            <label class="form-check-label text-warning" for="tipo-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Attenzione (Giallo)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="notifica-tipo" id="tipo-danger" value="danger">
                                            <label class="form-check-label text-danger" for="tipo-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Critico (Rosso)</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-accent btn-sm w-100 py-2" id="btn-submit-invia-notifica">
                                    <span class="spinner-border spinner-border-sm d-none me-2"></span>Invia Messaggio
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- jQuery & Bootstrap Bundle JS (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AJAX CSRF Setup -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toast Helper
        function showToast(message, type = 'success') {
            const toastEl = $('#liveToast');
            const messageEl = $('#toast-message');
            
            toastEl.removeClass('bg-success bg-danger bg-warning');
            
            if (type === 'success') {
                toastEl.addClass('bg-success');
            } else if (type === 'danger') {
                toastEl.addClass('bg-danger');
            } else {
                toastEl.addClass('bg-warning');
            }
            
            messageEl.text(message);
            const toast = new bootstrap.Toast(toastEl[0]);
            toast.show();
        }

        // Theme Toggle Logic
        $(document).ready(function() {
            const currentTheme = localStorage.getItem('theme') || 'dark';
            if (currentTheme === 'light') {
                $('#theme-icon').removeClass('fa-sun text-warning').addClass('fa-moon text-secondary');
            }

            $('#theme-toggle').on('click', function() {
                let theme = 'dark';
                if ($('html').attr('data-theme') !== 'light') {
                    $('html').attr('data-theme', 'light');
                    $('#theme-icon').removeClass('fa-sun text-warning').addClass('fa-moon text-secondary');
                    theme = 'light';
                } else {
                    $('html').removeAttr('data-theme');
                    $('#theme-icon').removeClass('fa-moon text-secondary').addClass('fa-sun text-warning');
                }
                localStorage.setItem('theme', theme);
            });
        });

        @auth
        // --- NOTIFICATIONS & MESSAGES SYSTEM JS ---

        $(document).ready(function() {
            @if(!Auth::user()->Richiede_Cambio_Password)
            // Initial count check
            checkUnreadCount();
            
            // Poll for new notifications every 30 seconds
            setInterval(checkUnreadCount, 30000);
            @endif

            // Modal show triggers
            $('#notificheModal').on('show.bs.modal', function() {
                loadNotifiche();
                
                // Reset form
                $('#form-invia-notifica')[0].reset();
                $('#notifica-utente-container').addClass('d-none');
                $('#notifica-utente-select').prop('required', false);
                
                // Set default tab back to Received
                const triggerEl = document.querySelector('#ricevute-tab');
                if (triggerEl) {
                    const tab = bootstrap.Tab.getOrCreateInstance(triggerEl);
                    tab.show();
                }
            });

            // Refresh dashboards when closing the notification modal
            $('#notificheModal').on('hidden.bs.modal', function() {
                refreshDashboardData();
            });

            // Single notification mark read
            $(document).on('click', '.btn-letta-singola', function() {
                const id = $(this).data('id');
                const btn = $(this);
                btn.prop('disabled', true);
                
                $.ajax({
                    url: `/notifiche/letta/${id}`,
                    method: 'POST',
                    success: function() {
                        loadNotifiche();
                        checkUnreadCount();
                        refreshDashboardData();
                    }
                });
            });

            // Mark all read
            $('#btn-segna-tutte-lette').on('click', function() {
                $.ajax({
                    url: '/notifiche/letta-tutte',
                    method: 'POST',
                    success: function() {
                        loadNotifiche();
                        checkUnreadCount();
                        refreshDashboardData();
                        showToast('Tutte le notifiche segnate come lette.');
                    }
                });
            });

            // Dynamically load users on Role select
            $('#notifica-ruolo-select').on('change', function() {
                const ruolo = $(this).val();
                const container = $('#notifica-utente-container');
                const select = $('#notifica-utente-select');
                
                if (!ruolo) {
                    container.addClass('d-none');
                    select.prop('required', false);
                    return;
                }
                
                select.empty().append('<option value="">Caricamento utenti...</option>');
                container.removeClass('d-none');
                select.prop('required', true);
                
                $.ajax({
                    url: '/notifiche/destinatari',
                    method: 'GET',
                    data: { ruolo: ruolo },
                    success: function(utenti) {
                        select.empty().append('<option value="">Seleziona utente...</option>');
                        if (utenti.length === 0) {
                            select.append('<option value="" disabled>Nessun utente disponibile (escluso te stesso)</option>');
                            return;
                        }
                        utenti.forEach(u => {
                            select.append(`<option value="${u.ID_Utente}">${u.Username}</option>`);
                        });
                        
                        // Select the pending reply recipient if set
                        if (window.pendingReplyToId) {
                            select.val(window.pendingReplyToId);
                            window.pendingReplyToId = null;
                        }
                    },
                    error: function() {
                        select.html('<option value="" disabled>Errore nel caricamento utenti</option>');
                    }
                });
            });

            // Send notification form submission
            $('#form-invia-notifica').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btn-submit-invia-notifica');
                const spinner = btn.find('.spinner-border');
                
                const idDestinatario = $('#notifica-utente-select').val();
                const titolo = $('#notifica-titolo').val();
                const messaggio = $('#notifica-messaggio').val();
                const tipo = $('input[name="notifica-tipo"]:checked').val();

                btn.prop('disabled', true);
                spinner.removeClass('d-none');

                $.ajax({
                    url: '/notifiche/invia-utente',
                    method: 'POST',
                    data: {
                        ID_Utente_Destinatario: idDestinatario,
                        Titolo: titolo,
                        Messaggio: messaggio,
                        Tipo: tipo
                    },
                    success: function(response) {
                        showToast(response.message || 'Messaggio inviato con successo!');
                        $('#form-invia-notifica')[0].reset();
                        $('#notifica-utente-container').addClass('d-none');
                        $('#notifica-utente-select').prop('required', false);
                        
                        // Switch tab back to received
                        const triggerEl = document.querySelector('#ricevute-tab');
                        if (triggerEl) {
                            const tab = bootstrap.Tab.getOrCreateInstance(triggerEl);
                            tab.show();
                        }
                        
                        loadNotifiche();
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');
                        let msg = 'Errore durante l\'invio del messaggio.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    }
                });
            });

            // Reply to notification click
            $(document).on('click', '.btn-rispondi-notifica', function() {
                const idMittente = $(this).data('id-mittente');
                const ruolo = $(this).data('ruolo');
                const titolo = $(this).data('titolo');

                // Switch to Invia tab
                const triggerEl = document.querySelector('#invia-tab');
                if (triggerEl) {
                    const tab = bootstrap.Tab.getOrCreateInstance(triggerEl);
                    tab.show();
                }

                // Select role
                $('#notifica-ruolo-select').val(ruolo).trigger('change');

                // Prepopulate title
                const cleanTitolo = titolo.startsWith('Re:') ? titolo : 'Re: ' + titolo;
                $('#notifica-titolo').val(cleanTitolo);

                // Queue up the selection of the user ID after AJAX finishes loading the user list
                window.pendingReplyToId = idMittente;
            });

            // Password change form submission
            $('#form-cambia-password').on('submit', function(e) {
                e.preventDefault();
                const newPass = $('#new_password_modal').val();
                const confPass = $('#new_password_modal_confirmation').val();

                if (newPass !== confPass) {
                    showToast('Le password inserite non coincidono.', 'danger');
                    return;
                }

                const btn = $('#btn-submit-change-password');
                btn.prop('disabled', true);

                $.ajax({
                    url: '/change-password',
                    method: 'POST',
                    data: {
                        new_password: newPass,
                        new_password_confirmation: confPass
                    },
                    success: function(response) {
                        showToast('Password aggiornata con successo!');
                        $('#cambiaPasswordModal').modal('hide');
                        $('#form-cambia-password')[0].reset();
                        btn.prop('disabled', false);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        let msg = 'Errore durante il cambio password.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    }
                });
            });
        });

        function checkUnreadCount() {
            $.ajax({
                url: '/notifiche',
                method: 'GET',
                success: function(notifiche) {
                    const unread = notifiche.filter(n => !n.Letta);
                    const badge = $('#badge-notifiche-unread');
                    if (unread.length > 0) {
                        badge.text(unread.length).removeClass('d-none');
                    } else {
                        badge.addClass('d-none');
                    }
                }
            });
        }

        function loadNotifiche() {
            const listContainer = $('#lista-notifiche');
            
            $.ajax({
                url: '/notifiche',
                method: 'GET',
                success: function(notifiche) {
                    listContainer.empty();
                    
                    if (notifiche.length === 0) {
                        listContainer.append(`
                            <div class="text-center py-5">
                                <i class="fa-regular fa-bell-slash fa-3x text-muted mb-3"></i>
                                <h6 class="text-white">Nessun messaggio</h6>
                                <p class="text-secondary small mb-0">Non hai ancora ricevuto alcuna notifica di sistema o messaggio.</p>
                            </div>
                        `);
                        return;
                    }
                    
                    notifiche.forEach(n => {
                        let borderClass = 'border-info';
                        let textClass = 'text-info';
                        let icon = 'fa-circle-info';
                        
                        if (n.Tipo === 'warning') {
                            borderClass = 'border-warning';
                            textClass = 'text-warning';
                            icon = 'fa-triangle-exclamation';
                        } else if (n.Tipo === 'danger') {
                            borderClass = 'border-danger';
                            textClass = 'text-danger';
                            icon = 'fa-circle-exclamation';
                        } else if (n.Tipo === 'success') {
                            borderClass = 'border-success';
                            textClass = 'text-success';
                            icon = 'fa-circle-check';
                        }
                        
                        // Parse timestamp
                        const dateObj = new Date(n.Data_Invio);
                        const dateStr = dateObj.toLocaleDateString('it-IT') + ' ' + dateObj.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
                        
                        const unreadStyle = !n.Letta ? 'style="background: rgba(255, 255, 255, 0.02) !important;"' : '';
                        
                        const btnHtml = !n.Letta ? `
                            <button class="btn btn-sm btn-outline-custom btn-letta-singola px-2 py-1" data-id="${n.ID_Notifica}" title="Segna come letta">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        ` : `
                            <span class="text-success small" style="opacity: 0.6; font-size: 0.75rem;"><i class="fa-solid fa-check-double me-1"></i>Letta</span>
                        `;
                        
                        // Check if sender exists
                        let senderHtml = '';
                        let replyBtnHtml = '';
                        if (n.mittente) {
                            senderHtml = `
                                <div class="text-secondary small mb-1" style="font-size: 0.75rem;">
                                    <i class="fa-regular fa-user me-1 text-accent"></i>Da: <strong class="text-light">${n.mittente.Username}</strong> 
                                    <span class="badge bg-secondary font-monospace small px-1.5 py-0.5 ms-1" style="font-size: 0.6rem !important;">${n.mittente.Ruolo}</span>
                                </div>
                            `;
                            
                            if (n.ID_Utente_Mittente !== {{ Auth::id() }}) {
                                replyBtnHtml = `
                                    <button class="btn btn-sm btn-outline-custom btn-rispondi-notifica px-2 py-1 me-1" 
                                            data-id-mittente="${n.ID_Utente_Mittente}" 
                                            data-username="${n.mittente.Username}" 
                                            data-ruolo="${n.mittente.Ruolo}" 
                                            data-titolo="${n.Titolo}" 
                                            title="Rispondi">
                                        <i class="fa-solid fa-reply"></i>
                                    </button>
                                `;
                            }
                        }
                        
                        const item = `
                            <div class="p-3 rounded border-start border-4 ${borderClass} d-flex justify-content-between align-items-start gap-3" ${unreadStyle} style="background: rgba(255, 255, 255, 0.01); border: 1px solid var(--card-border); border-left-width: 4px !important;">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-1 gap-2">
                                        <h6 class="mb-0 fw-bold ${textClass}" style="font-size: 0.85rem;"><i class="fa-solid ${icon} me-1.5"></i>${n.Titolo}</h6>
                                        <span class="text-secondary small" style="font-size: 0.75rem;">${dateStr}</span>
                                    </div>
                                    ${senderHtml}
                                    <p class="text-secondary mb-0 small" style="line-height: 1.4; font-size: 0.8rem;">${n.Messaggio}</p>
                                </div>
                                <div class="flex-shrink-0 pt-1 d-flex align-items-center">
                                    ${replyBtnHtml}
                                    ${btnHtml}
                                </div>
                            </div>
                        `;
                        listContainer.append(item);
                    });
                },
                error: function() {
                    listContainer.html(`
                        <div class="text-center text-danger py-4">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>Errore nel caricamento dei messaggi.
                        </div>
                    `);
                }
            });
        }

        function refreshDashboardData() {
            // Reload student dashboard list if function exists
            if (typeof loadLezioniProgrammate === 'function') {
                loadLezioniProgrammate();
            }
            if (typeof loadCorsiStudente === 'function') {
                loadCorsiStudente();
            }
            // Reload professor dashboard list if function exists
            if (typeof loadPrenotazioni === 'function') {
                loadPrenotazioni();
            }
            // Reload admin dashboard list if function exists
            if (typeof loadAule === 'function') {
                loadAule();
            }
        }
        @endauth
    </script>
    @yield('scripts')
</body>
</html>
