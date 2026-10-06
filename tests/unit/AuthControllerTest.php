<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Support/bootstrap.php';

use Nuartemo\Controllers\AuthController;
use Nuartemo\Models\Contracts\UserRepository;

$repository = new class implements UserRepository {
    public ?string $newPasswordHash = null;

    public function findByEmail(string $email): ?array
    {
        if ($email !== 'admin@example.test') {
            return null;
        }

        return ['id' => 3, 'nome' => 'Admin', 'senha' => 'senha-legada'];
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $this->newPasswordHash = $passwordHash;
    }
};

$controller = new AuthController($repository);
$user = $controller->authenticate('ADMIN@example.test', 'senha-legada');

assertSameValue(3, $user['id'] ?? null, 'Login deve localizar o usuário normalizando o e-mail.');
assertSameValue(true, password_verify('senha-legada', (string) $repository->newPasswordHash), 'Senha legada deve ser migrada para hash.');
assertSameValue(null, $controller->authenticate('admin@example.test', 'incorreta'), 'Senha incorreta deve falhar sem detalhes.');
assertSameValue(null, $controller->authenticate('invalido', 'senha-legada'), 'E-mail inválido deve falhar.');

echo "AuthControllerTest: ok\n";
