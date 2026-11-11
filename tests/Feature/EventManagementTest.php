<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_calendar_feed_returns_events(): void
    {
        $event = Event::query()->create([
            'title' => 'Ensaio aberto',
            'color' => '#228B22',
            'start_at' => '2026-11-20 14:00:00',
            'end_at' => '2026-11-20 16:00:00',
        ]);

        $this->getJson('/eventos/feed')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $event->id,
                'title' => 'Ensaio aberto',
                'color' => '#228B22',
            ]);
    }

    public function test_guest_cannot_manage_events(): void
    {
        $event = Event::query()->create([
            'title' => 'Evento existente',
            'color' => '#0071C5',
            'start_at' => '2026-11-20 14:00:00',
            'end_at' => '2026-11-20 16:00:00',
        ]);

        $this->post('/admin/eventos', $this->validPayload())
            ->assertRedirect('/login');
        $this->put("/admin/eventos/{$event->id}", $this->validPayload())
            ->assertRedirect('/login');
        $this->delete("/admin/eventos/{$event->id}")
            ->assertRedirect('/login');

        $this->assertDatabaseCount('events', 1);
    }

    public function test_authenticated_user_can_create_update_and_delete_an_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/admin/eventos', $this->validPayload())
            ->assertRedirect('/admin/eventos')
            ->assertSessionHas('status');

        $event = Event::query()->sole();
        $this->assertSame('Mostra cultural', $event->title);

        $this->actingAs($user)
            ->put("/admin/eventos/{$event->id}", [
                ...$this->validPayload(),
                'title' => 'Mostra cultural atualizada',
            ])->assertRedirect('/admin/eventos')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Mostra cultural atualizada',
        ]);

        $this->actingAs($user)
            ->delete("/admin/eventos/{$event->id}")
            ->assertRedirect('/admin/eventos')
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_event_input_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/admin/eventos')
            ->post('/admin/eventos', [
                'title' => '',
                'color' => 'javascript:alert(1)',
                'start_at' => 'não-é-data',
                'end_at' => '2020-01-01 00:00:00',
            ])->assertRedirect('/admin/eventos')
            ->assertSessionHasErrors(['title', 'color', 'start_at']);

        $this->actingAs($user)
            ->from('/admin/eventos')
            ->post('/admin/eventos', [
                ...$this->validPayload(),
                'start_at' => '2026-11-20 16:00:00',
                'end_at' => '2026-11-20 14:00:00',
            ])->assertRedirect('/admin/eventos')
            ->assertSessionHasErrors('end_at');

        $this->assertDatabaseCount('events', 0);
    }

    /** @return array<string, string> */
    private function validPayload(): array
    {
        return [
            'title' => 'Mostra cultural',
            'color' => '#8B0000',
            'start_at' => '2026-11-20 14:00:00',
            'end_at' => '2026-11-20 16:00:00',
        ];
    }
}
