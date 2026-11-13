<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->is_admin || $reservation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, Reservation $reservation): bool
    {
        return $user->is_admin && $reservation->status === 'pending';
    }

    public function reject(User $user, Reservation $reservation): bool
    {
        return $user->is_admin && $reservation->status === 'pending';
    }

    public function checkout(User $user, Reservation $reservation): bool
    {
        return $user->is_admin && $reservation->status === 'approved';
    }

    public function returnKey(User $user, Reservation $reservation): bool
    {
        return $user->is_admin && $reservation->status === 'checked_out';
    }
}