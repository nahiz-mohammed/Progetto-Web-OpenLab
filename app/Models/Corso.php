<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corso extends Model
{
    use HasFactory;

    protected $table = 'corso';
    protected $primaryKey = 'ID_Corso';
    public $timestamps = false;

    protected $fillable = [
        'Nome',
        'Tipologia_Materia',
        'ID_Professore',
    ];

    public function professore()
    {
        return $this->belongsTo(User::class, 'ID_Professore', 'ID_Utente');
    }

    public function prenotazioni()
    {
        return $this->hasMany(Prenotazione::class, 'ID_Corso', 'ID_Corso');
    }

    public function iscrizioni()
    {
        return $this->hasMany(IscrizioneCorso::class, 'ID_Corso', 'ID_Corso');
    }
}
