<?php

namespace App\Policies;

use App\Models\Credito;
use App\Models\User;

class CreditoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Credito $credito): bool
    {
        if ($user->isAdmin()) return true;
        return $credito->cobrador_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Credito $credito): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Credito $credito): bool
    {
        return $user->isAdmin();
    }
}
