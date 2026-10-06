<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/bootstrap.php';

use Nuartemo\Http\Security;
use Nuartemo\Controllers\EventController;
use Nuartemo\Models\Event;

Security::requireAuthentication(true);

try {
    $controller = new EventController(new Event(database()));
    Security::json($controller->index());
} catch (Throwable $error) {
    error_log($error->getMessage());
    Security::json(['status' => false, 'msg' => 'Não foi possível carregar os eventos.'], 500);
}
