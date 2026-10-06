<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/bootstrap.php';

use Nuartemo\Controllers\EventController;
use Nuartemo\Http\Security;
use Nuartemo\Models\Event;

Security::requireAuthentication(true);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
    Security::json(['status' => false, 'msg' => 'Requisição inválida.'], 403);
}

try {
    $controller = new EventController(new Event(database()));
    $event = $controller->create([
        'title' => $_POST['cad_title'] ?? '', 'color' => $_POST['cad_color'] ?? '',
        'start' => $_POST['cad_start'] ?? '', 'end' => $_POST['cad_end'] ?? '',
    ]);
    Security::json(['status' => true, 'msg' => 'Evento cadastrado com sucesso!'] + $event);
} catch (InvalidArgumentException $error) {
    Security::json(['status' => false, 'msg' => $error->getMessage()], 422);
} catch (Throwable $error) {
    error_log($error->getMessage());
    Security::json(['status' => false, 'msg' => 'Não foi possível cadastrar o evento.'], 500);
}
