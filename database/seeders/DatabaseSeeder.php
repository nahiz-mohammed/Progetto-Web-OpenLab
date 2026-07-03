<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Aula;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Inserisci Utenti di Test
        User::create([
            'Username' => 'admin',
            'Password' => Hash::make('password'),
            'Ruolo' => 'Admin',
            'Richiede_Cambio_Password' => 0
        ]);

        User::create([
            'Username' => 'prof_elettronica',
            'Password' => Hash::make('temp123'),
            'Ruolo' => 'Professore',
            'Richiede_Cambio_Password' => 1
        ]);

        User::create([
            'Username' => 'prof_informatica',
            'Password' => Hash::make('temp456'),
            'Ruolo' => 'Professore',
            'Richiede_Cambio_Password' => 1
        ]);

        User::create([
            'Username' => 'studente1',
            'Password' => Hash::make('password'),
            'Ruolo' => 'Studente',
            'Richiede_Cambio_Password' => 0
        ]);

        User::create([
            'Username' => 'studente2',
            'Password' => Hash::make('password'),
            'Ruolo' => 'Studente',
            'Richiede_Cambio_Password' => 0
        ]);

        // 2. Inserisci Aule di Test
        Aula::create([
            'Nome_Aula' => 'Aula A1',
            'Tipologia_Aula' => 'Teoria',
            'Capienza' => 50,
            'Stato' => 'Disponibile'
        ]);

        Aula::create([
            'Nome_Aula' => 'Laboratorio Elettronica',
            'Tipologia_Aula' => 'Elettronica',
            'Capienza' => 25,
            'Stato' => 'Disponibile'
        ]);

        Aula::create([
            'Nome_Aula' => 'Laboratorio PLC',
            'Tipologia_Aula' => 'Elettronica',
            'Capienza' => 20,
            'Stato' => 'Disponibile'
        ]);

        Aula::create([
            'Nome_Aula' => 'Laboratorio Informatica',
            'Tipologia_Aula' => 'Informatica',
            'Capienza' => 30,
            'Stato' => 'Disponibile'
        ]);

        Aula::create([
            'Nome_Aula' => 'Aula B2',
            'Tipologia_Aula' => 'Teoria',
            'Capienza' => 40,
            'Stato' => 'Manutenzione'
        ]);
    }
}
