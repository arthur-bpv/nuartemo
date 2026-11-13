<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SuapLoginController extends Controller
{
    private const AUTHORIZE_URL = 'https://suap.ifrn.edu.br/o/authorize/';

    private const TOKEN_URL = 'https://suap.ifrn.edu.br/o/token/';

    private const PROFILE_URL = 'https://suap.ifrn.edu.br/api/rh/eu/';

    public function redirect(Request $request): RedirectResponse
    {
        return $this->authorize($request, forKeyPanel: false);
    }

    public function keyPanel(Request $request): Response|RedirectResponse
    {
        if ($request->session()->has('suap.key_panel_identifier')) {
            return response()->view('pages.key-panel')->header('Cache-Control', 'private, no-store');
        }

        return $this->authorize($request, forKeyPanel: true);
    }

    private function authorize(Request $request, bool $forKeyPanel): RedirectResponse
    {
        abort_unless(config('services.suap.enabled'), 404);

        $state = Str::random(64);
        $verifier = Str::random(64);
        $request->session()->put('suap.oauth', [
            'state' => $state,
            'verifier' => $verifier,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'for_key_panel' => $forKeyPanel,
        ]);

        return redirect()->away(self::AUTHORIZE_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.suap.client_id'),
            'redirect_uri' => $this->callbackUrl(),
            'scope' => config('services.suap.scope'),
            'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request): Response|RedirectResponse
    {
        abort_unless(config('services.suap.enabled'), 404);

        $attempt = $request->session()->pull('suap.oauth');
        $state = $request->query('state');
        $code = $request->query('code');

        $retry = ($attempt['for_key_panel'] ?? false) ? 'key-panel' : 'suap.redirect';

        if (! $attempt || $attempt['expires_at'] <= now()->timestamp ||
            ! is_string($state) || ! hash_equals($attempt['state'], $state)) {
            return $this->failed('A sessão de autenticação SUAP expirou. Tente novamente.', $retry);
        }

        if ($request->query('error') === 'invalid_scope') {
            return $this->failed('O SUAP recusou o escopo de acesso solicitado. Confirme o escopo permitido para este cliente.', $retry);
        }

        if ($request->query->has('error') || ! is_string($code) || $code === '') {
            return $this->failed('A autorização no SUAP foi cancelada ou não pôde ser concluída.', $retry);
        }

        try {
            $client = Http::acceptJson()->withoutRedirecting()->connectTimeout(5)->timeout(10);

            $tokenResponse = $client->asForm()->post(self::TOKEN_URL, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->callbackUrl(),
                'client_id' => config('services.suap.client_id'),
                'client_secret' => config('services.suap.client_secret'),
                'code_verifier' => $attempt['verifier'],
            ]);

            $token = $tokenResponse->json('access_token');

            if (! $tokenResponse->successful() || ! is_string($token) || $token === '') {
                return $this->failed('Não foi possível concluir a autenticação com o SUAP. Tente novamente.', $retry);
            }

            $profile = $client->withToken($token)->get(self::PROFILE_URL);
        } catch (ConnectionException) {
            return $this->failed('Não foi possível conectar ao SUAP. Tente novamente.', $retry);
        }

        $identifier = $profile->json('identificacao');

        if (! $profile->successful() || ! is_string($identifier) || trim($identifier) === '') {
            return $this->failed('O SUAP não retornou uma identificação válida.', $retry);
        }

        $identifier = strtoupper(trim($identifier));

        if ($attempt['for_key_panel'] ?? false) {
            $request->session()->regenerate();
            $request->session()->put('suap.key_panel_identifier', $identifier);

            return to_route('key-panel');
        }

        $user = User::query()->where('suap_identifier', $identifier)->first();

        if (! $user) {
            return $this->failed('Esta conta SUAP ainda não tem acesso ao NUARTE.', $retry);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.events.index', absolute: false));
    }

    private function callbackUrl(): string
    {
        return rtrim(config('app.url'), '/').route('suap.callback', absolute: false);
    }

    private function failed(string $message, string $retry): Response
    {
        return response()->view('auth.suap-error', compact('message', 'retry'), 400)
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
