<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifica extends Model
{
    use HasFactory;

    protected $table = 'notifica';
    protected $primaryKey = 'ID_Notifica';
    public $timestamps = false;

    protected $fillable = [
        'ID_Utente_Destinatario',
        'ID_Utente_Mittente',
        'Titolo',
        'Messaggio',
        'Tipo',
        'Letta',
        'Data_Invio',
    ];

    public function destinatario()
    {
        return $this->belongsTo(User::class, 'ID_Utente_Destinatario', 'ID_Utente');
    }

    public function mittente()
    {
        return $this->belongsTo(User::class, 'ID_Utente_Mittente', 'ID_Utente');
    }
}
