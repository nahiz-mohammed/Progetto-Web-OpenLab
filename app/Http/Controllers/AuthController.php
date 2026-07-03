<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->getRedirectRoute(Auth::user()->Ruolo));
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'Username' => 'required|string',
            'Password' => 'required|string',
        ]);

        $user = User::where('Username', $request->Username)->first();

        if ($user && Hash::check($request->Password, $user->Password)) {
            Auth::login($user);

            return response()->json([
                'success' => true,
                'role' => $user->Ruolo,
                'requires_password_change' => (bool)$user->Richiede_Cambio_Password,
                'redirect' => (bool)$user->Richiede_Cambio_Password ? route('change.password') : $this->getRedirectRoute($user->Ruolo)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenziali non valide.'
        ], 401);
    }

    public function showChangePassword()
    {
        if (!Auth::check()) {
            return redirect('/');
        }
        return view('change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato.'], 403);
        }

        $user->Password = Hash::make($request->new_password);
        $user->Richiede_Cambio_Password = 0;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password aggiornata con successo.',
            'redirect' => $this->getRedirectRoute($user->Ruolo)
        ]);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }

    private function getRedirectRoute($role)
    {
        switch ($role) {
            case 'Admin':
                return '/admin';
            case 'Professore':
                return '/professore';
            case 'Studente':
                return '/studente';
            default:
                return '/';
        }
    }
}
