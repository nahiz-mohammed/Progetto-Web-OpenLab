<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IscrizioneCorso extends Model
{
    use HasFactory;

    protected $table = 'iscrizione_corso';
    protected $primaryKey = 'ID_Iscrizione';
    public $timestamps = false;

    protected $fillable = [
        'ID_Studente',
        'ID_Corso',
        'Stato',
        'Data_Richiesta',
    ];

    public function studente()
    {
        return $this->belongsTo(User::class, 'ID_Studente', 'ID_Utente');
    }

    public function corso()
    {
        return $this->belongsTo(Corso::class, 'ID_Corso', 'ID_Corso');
    }
}
