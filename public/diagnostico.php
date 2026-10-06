<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Support/bootstrap.php';

use Nuartemo\Controllers\DiagnosticsController;
use Nuartemo\Http\Security;
use Nuartemo\Models\SystemStatus;
use Nuartemo\Support\View;

Security::requireAuthentication();

if (env('APP_ENV', 'production') !== 'development' || env('DIAGNOSTICS_ENABLED') !== 'true') {
    http_response_code(404);
    exit;
}

$controller = new DiagnosticsController(new SystemStatus(database()));
View::render('system/diagnostics', $controller->data());
