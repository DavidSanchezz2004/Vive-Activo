<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required','email'],
      'password' => ['required','string'],
    ]);

    if (Auth::attempt($credentials, false)) {
      $request->session()->regenerate();

      $user = Auth::user();

      return match ($user->role) {
        'admin' => redirect('/admin'),
        'supervisor' => redirect('/supervisor'),
        'alumno' => redirect('/estudiante'),
        default => redirect('/paciente'),
      };
    }

    return back()->withErrors([
      'email' => 'Credenciales inválidas.',
    ])->onlyInput('email');
  }

  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
  }
}
