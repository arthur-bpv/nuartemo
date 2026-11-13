<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationCheckoutController extends Controller
{
    public function checkout(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('checkout', $reservation);

        return $this->updateStatus($request, $reservation, 'checked_out');
    }

    public function returnKey(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('returnKey', $reservation);

        return $this->updateStatus($request, $reservation, 'returned');
    }

    private function updateStatus(
        Request $request,
        Reservation $reservation,
        string $status
    ): JsonResponse {
        return DB::transaction(function () use ($request, $reservation, $status): JsonResponse {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            $allowedStatuses = $status === 'checked_out'
                ? ['approved']
                : ['checked_out'];

            if (! in_array($reservation->status, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'status' => $status === 'checked_out'
                        ? 'Somente reservas aprovadas podem registrar retirada.'
                        : 'Somente chaves retiradas podem ser devolvidas.',
                ]);
            }

            $reservation->update([
                'status' => $status,
                'notes' => $request->input('notes', $reservation->notes),
                'checked_out_at' => $status === 'checked_out'
                    ? now()
                    : $reservation->checked_out_at,
                'returned_at' => $status === 'returned'
                    ? now()
                    : $reservation->returned_at,
            ]);

            return response()->json([
                'data' => $reservation->fresh()->load(['key:id,name', 'user:id,name']),
                'message' => $status === 'checked_out'
                    ? 'Retirada registrada com sucesso.'
                    : 'Devolução registrada com sucesso.',
            ]);
        });
    }
}