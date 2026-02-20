<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
  public function show()
  {
    $user = auth()->user();

    // crea el perfil si aún no existe
    $user->profile()->firstOrCreate([]);

    return view('profile.show', [
      'user' => $user->fresh('profile'),
    ]);
  }

  public function updatePersonal(Request $request)
  {
    $user = auth()->user();
    $profile = $user->profile()->firstOrCreate([]);

    $data = $request->validate([
      'first_name' => ['nullable','string','max:80'],
      'last_name'  => ['nullable','string','max:80'],
      'phone'      => ['nullable','string','max:30'],
      'document_type' => ['nullable', Rule::in(['DNI','CE','PAS'])],
      'document_number' => ['nullable','string','max:20'],
    ]);

    $profile->update($data);

    return back()->with('success', 'Información personal actualizada');
  }

  public function updateAddress(Request $request)
  {
    $user = auth()->user();
    $profile = $user->profile()->firstOrCreate([]);

    $data = $request->validate([
      'country' => ['nullable','string','max:80'],
      'region'  => ['nullable','string','max:120'],
      'district'=> ['nullable','string','max:120'],
      'address_line'=> ['nullable','string','max:160'],
    ]);

    $profile->update($data);

    return back()->with('success', 'Dirección actualizada');
  }

  public function updateAvatar(Request $request)
  {
    $user = auth()->user();
    $profile = $user->profile()->firstOrCreate([]);

    $request->validate(
    [
      'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:512',
    ],
    [
      'avatar.required' => 'Por favor selecciona una foto.',
      'avatar.image'    => 'El archivo debe ser una imagen válida.',
      'avatar.mimes'    => 'Formatos permitidos: JPG, JPEG, PNG o WEBP.',
      'avatar.max'      => 'La foto es muy pesada. El máximo permitido es 512 KB. Prueba con una más liviana o recórtala.',
    ]
  );

    // borrar anterior
    if ($profile->avatar_path) {
      Storage::disk('public')->delete($profile->avatar_path);
    }

    // guardar nueva (rápido, sin compresión)
    $path = $request->file('avatar')->store('avatars', 'public');

    $profile->update(['avatar_path' => $path]);

    return back()->with('success', 'Foto actualizada');
  }
}