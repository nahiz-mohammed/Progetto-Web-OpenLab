<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfermaPresenza extends Model
{
    use HasFactory;

    protected $table = 'conferma_presenza';
    protected $primaryKey = 'ID_Conferma';
    public $timestamps = false;

    protected $fillable = [
        'ID_Studente',
        'ID_Prenotazione',
        'Confermata',
        'Timestamp_Conferma',
    ];

    public function studente()
    {
        return $this->belongsTo(User::class, 'ID_Studente', 'ID_Utente');
    }

    public function prenotazione()
    {
        return $this->belongsTo(Prenotazione::class, 'ID_Prenotazione', 'ID_Prenotazione');
    }
}
