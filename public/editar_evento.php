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

$id = filter_var($_POST['edit_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    Security::json(['status' => false, 'msg' => 'Identificador de evento inválido.'], 422);
}

try {
    $controller = new EventController(new Event(database()));
    $event = $controller->update((int) $id, [
        'title' => $_POST['edit_title'] ?? '', 'color' => $_POST['edit_color'] ?? '',
        'start' => $_POST['edit_start'] ?? '', 'end' => $_POST['edit_end'] ?? '',
    ]);
    Security::json(['status' => true, 'msg' => 'Evento editado com sucesso!'] + $event);
} catch (InvalidArgumentException $error) {
    Security::json(['status' => false, 'msg' => $error->getMessage()], 422);
} catch (Throwable $error) {
    error_log($error->getMessage());
    Security::json(['status' => false, 'msg' => 'Não foi possível editar o evento.'], 500);
}
