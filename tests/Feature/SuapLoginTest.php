<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SuapLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_routes_are_disabled_without_credentials(): void
    {
        config(['services.suap.enabled' => false]);

        $this->get('/login')->assertNotFound();
        $this->get('/auth/suap')->assertNotFound();
        $this->get('/auth/suap/callback')->assertNotFound();
    }

    public function test_suap_logs_in_the_linked_account_with_state_and_pkce(): void
    {
        $user = User::factory()->create();
        $this->artisan('suap:link', ['email' => $user->email, 'identificador' => 'A123'])->assertSuccessful();

        $query = $this->startLogin();
        $callback = 'https://nuartemo.ifrn.edu.br/auth/suap/callback';
        $this->assertSame($callback, $query['redirect_uri']);
        $this->assertSame('code', $query['response_type']);
        $this->assertSame('S256', $query['code_challenge_method']);
        Http::preventStrayRequests();
        Http::fake([
            'https://suap.ifrn.edu.br/o/token/' => Http::response(['access_token' => 'test-token']),
            'https://suap.ifrn.edu.br/api/rh/eu/' => Http::response(['identificacao' => 'A123']),
        ]);

        $this->get(route('suap.callback', ['code' => 'test-code', 'state' => $query['state']], false))
            ->assertRedirect('/admin/eventos')
            ->assertSessionMissing('suap.oauth');

        $this->assertAuthenticatedAs($user);
        Http::assertSent(function (Request $request) use ($query, $callback): bool {
            return $request->url() === 'https://suap.ifrn.edu.br/o/token/'
                && $request['redirect_uri'] === $callback
                && $request['code'] === 'test-code'
                && $request['client_secret'] === 'test-secret'
                && rtrim(strtr(base64_encode(hash('sha256', $request['code_verifier'], true)), '+/', '-_'), '=') === $query['code_challenge'];
        });
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://suap.ifrn.edu.br/api/rh/eu/'
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_callback_rejects_an_invalid_or_consumed_state_before_contacting_suap(): void
    {
        $query = $this->startLogin();
        Http::preventStrayRequests();

        foreach (['invalid-state', $query['state']] as $state) {
            $this->get(route('suap.callback', ['code' => 'test-code', 'state' => $state], false))
                ->assertBadRequest()
                ->assertSee('A sessão de autenticação SUAP expirou.');
        }

        Http::assertNothingSent();
        $this->assertGuest();
    }

    public function test_suap_identity_with_a_matching_email_cannot_claim_an_unlinked_account(): void
    {
        $user = User::factory()->create();
        $query = $this->startLogin();
        Http::preventStrayRequests();
        Http::fake([
            'https://suap.ifrn.edu.br/o/token/' => Http::response(['access_token' => 'test-token']),
            'https://suap.ifrn.edu.br/api/rh/eu/' => Http::response(['identificacao' => 'B456', 'email' => $user->email]),
        ]);

        $this->get(route('suap.callback', ['code' => 'test-code', 'state' => $query['state']], false))
            ->assertBadRequest()
            ->assertSee('Esta conta SUAP ainda não tem acesso ao NUARTE.');

        $this->assertGuest();
        $this->assertNull($user->refresh()->suap_identifier);
    }

    public function test_key_panel_requires_suap_and_keeps_agenda_access_separate(): void
    {
        $query = $this->startLogin('/painel-chaves');
        Http::preventStrayRequests();
        Http::fake([
            'https://suap.ifrn.edu.br/o/token/' => Http::response(['access_token' => 'test-token']),
            'https://suap.ifrn.edu.br/api/rh/eu/' => Http::response(['identificacao' => 'B456']),
        ]);

        $this->get(route('suap.callback', ['code' => 'test-code', 'state' => $query['state']], false))
            ->assertRedirect('/painel-chaves');

        $this->get('/painel-chaves')->assertOk()->assertSee('ChaveNUARTE');
        $this->assertGuest();
        $this->get('/admin/eventos')->assertRedirect('/login');
    }

    public function test_rejected_scope_explains_the_error_and_retries_the_panel_flow(): void
    {
        $query = $this->startLogin('/painel-chaves');
        Http::preventStrayRequests();

        $this->get(route('suap.callback', ['error' => 'invalid_scope', 'state' => $query['state']], false))
            ->assertBadRequest()
            ->assertSee('O SUAP recusou o escopo de acesso solicitado.')
            ->assertSee(route('key-panel'))
            ->assertDontSee('Senha');

        Http::assertNothingSent();
    }

    private function startLogin(string $path = '/auth/suap'): array
    {
        config([
            'app.url' => 'https://nuartemo.ifrn.edu.br',
            'services.suap.enabled' => true,
            'services.suap.client_id' => 'test-client',
            'services.suap.client_secret' => 'test-secret',
        ]);

        $response = $this->get($path);
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://suap.ifrn.edu.br/o/authorize/?', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        return $query;
    }
}
