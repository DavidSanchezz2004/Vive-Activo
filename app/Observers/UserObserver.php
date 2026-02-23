<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\Student;
use App\Models\User;

class UserObserver
{
    /**
     * Se ejecuta después de crear un user.
     */
    public function created(User $user): void
    {
        $this->syncDomainProfile($user);
    }

    /**
     * Se ejecuta después de actualizar un user.
     * Solo actúa si cambió el role.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('role')) {
            $this->syncDomainProfile($user);
        }
    }

    private function syncDomainProfile(User $user): void
    {
        // Normaliza a enum aunque venga como string
        $role = $user->role instanceof UserRole
            ? $user->role
            : UserRole::from((string) $user->role);

        if ($role === UserRole::Patient) {
            Patient::firstOrCreate(
                ['user_id' => $user->id],
                ['is_active' => true]
            );
        }

        if ($role === UserRole::Student) {
            Student::firstOrCreate(
                ['user_id' => $user->id],
                ['is_active' => true]
            );
        }

        // Admin y Supervisor no necesitan perfil de dominio
    }
}
