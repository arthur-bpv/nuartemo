<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Entrar');
    }

    public function test_user_can_authenticate_and_session_is_regenerated(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'uma-senha-segura',
        ]);

        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'uma-senha-segura',
        ])->assertRedirect('/admin/eventos');

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_invalid_credentials_are_rejected_without_leaking_details(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'senha-correta',
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'senha-incorreta',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
