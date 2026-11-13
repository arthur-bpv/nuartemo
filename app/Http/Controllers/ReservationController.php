<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reservations = Reservation::query()
            ->with(['key:id,name', 'user:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest('starts_at')
            ->get();

        return response()->json(['data' => $reservations]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key_id' => ['required', 'integer', 'exists:keys,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'purpose' => ['required', 'string', 'max:255'],
        ]);

        $reservation = DB::transaction(function () use ($request, $validated): Reservation {
            $key = Key::query()
                ->whereKey($validated['key_id'])
                ->where('active', true)
                ->lockForUpdate()
                ->first();

            if (! $key) {
                throw ValidationException::withMessages([
                    'key_id' => 'A chave selecionada não está ativa.',
                ]);
            }

            $hasConflict = Reservation::query()
                ->where('key_id', $key->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('starts_at', '<', $validated['ends_at'])
                ->where('ends_at', '>', $validated['starts_at'])
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'starts_at' => 'A chave já possui uma reserva nesse período.',
                ]);
            }

            return Reservation::query()->create([
                ...$validated,
                'status' => 'pending',
                'user_id' => $request->user()->id,
            ]);
        });

        return response()->json([
            'data' => $reservation->load('key:id,name'),
            'message' => 'Solicitação de reserva enviada para aprovação.',
        ], 201);
    }
}
