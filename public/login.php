<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Support/bootstrap.php';

use Nuartemo\Controllers\AuthController;
use Nuartemo\Http\Security;
use Nuartemo\Models\User;
use Nuartemo\Support\View;

Security::startSession();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $message = 'Requisição inválida. Atualize a página e tente novamente.';
    } else {
        try {
            $controller = new AuthController(new User(database()));
            $user = $controller->authenticate((string) ($_POST['email'] ?? ''), (string) ($_POST['senha'] ?? ''));

            if ($user !== null) {
                session_regenerate_id(true);
                $_SESSION['id'] = $user['id'];
                $_SESSION['nome'] = $user['nome'];
                header('Location: eventos2.php', true, 302);
                exit;
            }

            $message = 'E-mail ou senha incorretos.';
        } catch (Throwable $error) {
            error_log($error->getMessage());
            $message = 'Não foi possível concluir o login agora.';
        }
    }
}

View::render('auth/login', ['message' => $message, 'csrfToken' => Security::csrfToken()]);
