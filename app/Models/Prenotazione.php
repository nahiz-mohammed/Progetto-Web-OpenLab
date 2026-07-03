<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prenotazione extends Model
{
    use HasFactory;

    protected $table = 'prenotazione';
    protected $primaryKey = 'ID_Prenotazione';
    public $timestamps = false;

    protected $fillable = [
        'Data',
        'Ora_Inizio',
        'Ora_Fine',
        'ID_Corso',
        'ID_Aula',
    ];

    public function corso()
    {
        return $this->belongsTo(Corso::class, 'ID_Corso', 'ID_Corso');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'ID_Aula', 'ID_Aula');
    }

    public function confermePresenza()
    {
        return $this->hasMany(ConfermaPresenza::class, 'ID_Prenotazione', 'ID_Prenotazione');
    }
}
