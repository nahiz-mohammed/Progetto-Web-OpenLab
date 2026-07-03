<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'utente';
    protected $primaryKey = 'ID_Utente';
    public $timestamps = false;

    protected $fillable = [
        'Username',
        'Password',
        'Ruolo',
        'Richiede_Cambio_Password',
    ];

    protected $hidden = [
        'Password',
    ];

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function getAuthIdentifierName()
    {
        return 'ID_Utente';
    }

    public function corsi()
    {
        return $this->hasMany(Corso::class, 'ID_Professore', 'ID_Utente');
    }

    public function iscrizioni()
    {
        return $this->hasMany(IscrizioneCorso::class, 'ID_Studente', 'ID_Utente');
    }

    public function confermePresenza()
    {
        return $this->hasMany(ConfermaPresenza::class, 'ID_Studente', 'ID_Utente');
    }
}
