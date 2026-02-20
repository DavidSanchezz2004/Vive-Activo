<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $role = $request->string('role')->toString(); // filtro opcional

        $users = User::query()
            ->when($q, fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            }))
            ->when($role, fn ($query) => $query->where('role', $role))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // roles para pintar el filtro del index (si lo necesitas)
        $roles = UserRole::cases();

        // KPIs (opcionales, por si tu index los muestra)
        $kpis = [
            'total' => User::count(),
            'admins' => User::where('role', UserRole::Admin)->count(),
            'supervisors' => User::where('role', UserRole::Supervisor)->count(),
            'others' => User::whereIn('role', [UserRole::Student, UserRole::Patient])->count(),
        ];

        return view('admin.users.index', compact('users', 'q', 'role', 'roles', 'kpis'));
    }

    public function create()
    {
        $roles = UserRole::cases(); // pasamos enums, no strings
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', new Enum(UserRole::class)],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            // Como tu User model castea role a UserRole, puedes guardar el string y listo:
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('ok', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $roles = UserRole::cases();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', "unique:users,email,{$user->id}"],
            'role' => ['required', new Enum(UserRole::class)],
            // password opcional
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Regla de seguridad: evitar dejar el sistema sin admins
        // $user->role es Enum (por cast), $data['role'] es string => comparamos con value
        if ($user->role === UserRole::Admin && $data['role'] !== UserRole::Admin->value) {
            $adminsCount = User::where('role', UserRole::Admin)->count();
            if ($adminsCount <= 1) {
                return back()
                    ->withErrors(['role' => 'Debe existir al menos 1 administrador.'])
                    ->withInput();
            }
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role']; // el cast lo convertirá a Enum

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('ok', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        // Evitar borrar el último admin
        if ($user->role === UserRole::Admin) {
            $adminsCount = User::where('role', UserRole::Admin)->count();
            if ($adminsCount <= 1) {
                return back()->withErrors(['delete' => 'No puedes eliminar el último administrador.']);
            }
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('ok', 'Usuario eliminado.');
    }
}