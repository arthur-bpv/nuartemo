<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationApprovalController extends Controller
{
    public function approve(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('approve', $reservation);

        return $this->updateStatus($request, $reservation, 'approved');
    }

    public function reject(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('reject', $reservation);

        return $this->updateStatus($request, $reservation, 'rejected');
    }

    private function updateStatus(
        Request $request,
        Reservation $reservation,
        string $status
    ): JsonResponse {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($request, $reservation, $status, $validated): JsonResponse {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            // Confere de novo dentro da transação para evitar alteração concorrente.
            $this->authorize($status === 'approved' ? 'approve' : 'reject', $reservation);

            $reservation->update([
                'status' => $status,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'data' => $reservation->fresh()->load(['key:id,name', 'user:id,name']),
                'message' => $status === 'approved'
                    ? 'Reserva aprovada com sucesso.'
                    : 'Solicitação rejeitada.',
            ]);
        });
    }
}