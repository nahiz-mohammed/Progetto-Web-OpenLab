@extends('layouts.app')

@section('title', 'Cambio Password Obbligatorio - OpenLab')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-12 d-flex justify-content-center">
        <div class="auth-card-wrapper">
            <div class="glass-card p-4 p-sm-5 animate-fade-in">
            <div class="text-center mb-4">
                <i class="fa-solid fa-key fa-3x text-warning mb-3 animate-pulse"></i>
                <h2 class="display-font text-white mb-1">Nuova Password</h2>
                <p class="text-secondary">È richiesto il cambio della password temporanea prima di procedere.</p>
            </div>
            
            <div class="alert alert-danger d-none" id="password-error-alert" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><span id="error-message"></span>
            </div>

            <form id="change-password-form">
                @csrf
                <div class="mb-4">
                    <label for="new_password" class="form-label text-secondary small uppercase fw-bold">Nuova Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--card-border); color: var(--text-secondary);">
                            <i class="fa-solid fa-lock-open"></i>
                        </span>
                        <input type="password" class="form-control border-start-0 ps-0" id="new_password" name="new_password" placeholder="Minimo 6 caratteri" required autocomplete="new-password">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="new_password_confirmation" class="form-label text-secondary small uppercase fw-bold">Conferma Nuova Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--card-border); color: var(--text-secondary);">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                        <input type="password" class="form-control border-start-0 ps-0" id="new_password_confirmation" name="new_password_confirmation" placeholder="Ripeti la password" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100 py-3 mt-2" id="btn-submit">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                    <i class="fa-solid fa-floppy-disk me-2"></i>Aggiorna Password
                </button>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#change-password-form').on('submit', function(e) {
            e.preventDefault();
            
            const password = $('#new_password').val();
            const passwordConf = $('#new_password_confirmation').val();
            const submitBtn = $('#btn-submit');
            const spinner = submitBtn.find('.spinner-border');
            const errorAlert = $('#password-error-alert');
            
            if (password.length < 6) {
                errorAlert.removeClass('d-none');
                $('#error-message').text('La password deve essere di almeno 6 caratteri.');
                return;
            }

            if (password !== passwordConf) {
                errorAlert.removeClass('d-none');
                $('#error-message').text('Le password non coincidono.');
                return;
            }

            errorAlert.addClass('d-none');
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            
            $.ajax({
                url: '/change-password',
                method: 'POST',
                data: {
                    new_password: password,
                    new_password_confirmation: passwordConf
                },
                success: function(response) {
                    showToast('Password aggiornata con successo! Reindirizzamento...', 'success');
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1200);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    let errorMessage = 'Errore di connessione. Riprova.';
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
