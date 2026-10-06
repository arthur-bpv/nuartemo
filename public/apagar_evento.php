<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/bootstrap.php';

use Nuartemo\Http\Security;
use Nuartemo\Controllers\EventController;
use Nuartemo\Models\Event;

Security::requireAuthentication(true);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
    Security::json(['status' => false, 'msg' => 'Requisição inválida.'], 403);
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    Security::json(['status' => false, 'msg' => 'Identificador de evento inválido.'], 422);
}

try {
    $controller = new EventController(new Event(database()));
    $controller->delete((int) $id);
    Security::json(['status' => true, 'msg' => 'Evento apagado com sucesso!']);
} catch (Throwable $error) {
    error_log($error->getMessage());
    Security::json(['status' => false, 'msg' => 'Não foi possível apagar o evento.'], 500);
}
