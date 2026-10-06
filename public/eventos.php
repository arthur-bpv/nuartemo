<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Support/bootstrap.php';

use Nuartemo\Http\Security;
use Nuartemo\Support\View;

Security::requireAuthentication();
View::render('events/manage', ['csrfToken' => Security::csrfToken()]);
