<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Loan $loan): bool
    {
        return $user->isAdmin() || $user->id === $loan->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->isAdmin() || $user->id === $loan->user_id;
    }

    public function delete(User $user, Loan $loan): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Loan $loan): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Loan $loan): bool
    {
        return $user->isAdmin();
    }

    public function exportCsv(User $user): bool
    {
        return $user->isAdmin();
    }
}
