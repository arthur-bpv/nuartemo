<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\SuapLoginController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');

Route::get('/eventos', [PublicEventController::class, 'index'])->name('events.public');
Route::get('/eventos/feed', [PublicEventController::class, 'feed'])->name('events.feed');
Route::get('/painel-chaves', [SuapLoginController::class, 'keyPanel'])->name('key-panel');
Route::get('/auth/suap/callback', [SuapLoginController::class, 'callback'])->name('suap.callback');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SuapLoginController::class, 'redirect'])->name('login');
    Route::get('/auth/suap', [SuapLoginController::class, 'redirect'])->name('suap.redirect');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('eventos', EventController::class)
        ->parameters(['eventos' => 'event'])
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('events');
});

$legacyPages = [
    'a_cicuta' => 'a_cicuta',
    'about' => 'about',
    'andaluz' => 'andaluz',
    'artevivencias' => 'artevivencias',
    'banda_descendentes' => 'banda_descendentes',
    'banda_sn' => 'banda_sn',
    'clube_de_escrita' => 'clube_de_escrita',
    'clube_do_livro' => 'clube_do_livro',
    'flauteando' => 'flauteando',
    'nuav' => 'nuav',
    'passocompasso' => 'passocompasso',
    'rabisco' => 'rabisco',
    'thank_you' => 'thank_you',
];

foreach ($legacyPages as $path => $view) {
    Route::view("/{$path}.html", "pages.{$view}");
}

Route::redirect('/index.html', '/');
Route::redirect('/eventos.php', '/eventos');
Route::redirect('/eventos2.php', '/admin/eventos');
Route::redirect('/login.php', '/login');
Route::redirect('/listar_evento.php', '/eventos/feed');
