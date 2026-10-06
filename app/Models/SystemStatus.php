<?php
declare(strict_types=1);
namespace Nuartemo\Models;

use PDO;
use Throwable;

final class SystemStatus
{
    public function __construct(private readonly PDO $database) {}

    public function databaseStatus(): string
    {
        try {
            $this->database->query('SELECT 1');
            return 'conectado';
        } catch (Throwable $error) {
            error_log($error->getMessage());
            return 'indisponível';
        }
    }
}
