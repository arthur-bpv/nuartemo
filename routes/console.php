<?php

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('suap:link {email} {identificador}', function (): int {
    $identifier = strtoupper(trim($this->argument('identificador')));

    if (! preg_match('/\A[A-Z0-9]{1,64}\z/', $identifier)) {
        $this->error('Informe um identificador SUAP alfanumérico válido.');

        return 1;
    }

    $user = User::query()->where('email', $this->argument('email'))->first();

    if (! $user) {
        $this->error('Conta local não encontrada.');

        return 1;
    }

    try {
        $user->suap_identifier = $identifier;
        $user->save();
    } catch (UniqueConstraintViolationException) {
        $this->error('Este identificador SUAP já está associado a outra conta.');

        return 1;
    }

    $this->info('Conta vinculada ao SUAP.');

    return 0;
})->purpose('Vincula uma conta local autorizada ao identificador do SUAP');
