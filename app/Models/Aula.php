<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aula';
    protected $primaryKey = 'ID_Aula';
    public $timestamps = false;

    protected $fillable = [
        'Nome_Aula',
        'Tipologia_Aula',
        'Capienza',
        'Stato',
    ];

    public function prenotazioni()
    {
        return $this->hasMany(Prenotazione::class, 'ID_Aula', 'ID_Aula');
    }
}
