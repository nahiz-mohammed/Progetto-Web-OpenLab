@extends('layouts.app')

@section('title', 'Login - OpenLab')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-12 d-flex justify-content-center">
        <div class="auth-card-wrapper">
            <div class="glass-card p-4 p-sm-5 animate-fade-in">
            <div class="text-center mb-4">
                <i class="fa-solid fa-graduation-cap fa-3x text-accent mb-3"></i>
                <h2 class="display-font text-white mb-1">Benvenuto</h2>
                <p class="text-secondary">Accedi al portale OpenLab</p>
            </div>
            
            <div class="alert alert-danger d-none" id="login-error-alert" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><span id="error-message"></span>
            </div>

            <form id="login-form">
                @csrf
                <div class="mb-4">
                    <label for="Username" class="form-label text-secondary small uppercasefw-bold">Nome Utente</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--card-border); color: var(--text-secondary);">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="Username" name="Username" placeholder="Es. admin, prof_rossi, matteo_99" required autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="Password" class="form-label text-secondary small uppercase fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--card-border); color: var(--text-secondary);">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" class="form-control border-start-0 ps-0" id="Password" name="Password" placeholder="Inserisci la password" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100 py-3 mt-2" id="btn-login-submit">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Accedi
                </button>
            </form>
            
            <div class="mt-4 pt-3 border-top text-center text-secondary small" style="border-color: var(--card-border) !important;">
                <span>Credenziali di test:<br>
                Admin: <code>admin</code> / <code>password</code><br>
                Professore: <code>prof_elettronica</code> / <code>temp123</code><br>
                Studente: <code>studente1</code> / <code>password</code></span>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#login-form').on('submit', function(e) {
            e.preventDefault();
            
            const username = $('#Username').val();
            const password = $('#Password').val();
            const submitBtn = $('#btn-login-submit');
            const spinner = submitBtn.find('.spinner-border');
            const errorAlert = $('#login-error-alert');
            
            errorAlert.addClass('d-none');
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            
            $.ajax({
                url: '/login',
                method: 'POST',
                data: {
                    Username: username,
                    Password: password
                },
                success: function(response) {
                    showToast('Login effettuato con successo! Reindirizzamento in corso...', 'success');
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1200);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    let errorMessage = 'Errore di connessione. Riprova più tardi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    errorAlert.removeClass('d-none');
                    $('#error-message').text(errorMessage);
                    showToast(errorMessage, 'danger');
                }
            });
        });
    });
</script>
@endsection
