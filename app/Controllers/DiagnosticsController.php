<?php
declare(strict_types=1);
namespace Nuartemo\Controllers;

use Nuartemo\Models\SystemStatus;

final class DiagnosticsController
{
    public function __construct(private readonly SystemStatus $systemStatus) {}

    public function data(): array
    {
        return [
            'databaseStatus' => $this->systemStatus->databaseStatus(),
            'extensions' => ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'session'],
            'apacheModules' => function_exists('apache_get_modules') ? apache_get_modules() : [],
        ];
    }
}
