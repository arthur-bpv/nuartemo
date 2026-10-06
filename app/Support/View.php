<?php
declare(strict_types=1);
namespace Nuartemo\Support;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $path = dirname(__DIR__) . '/Views/' . $view . '.php';
        if (!is_file($path)) throw new RuntimeException("View não encontrada: {$view}");
        extract($data, EXTR_SKIP);
        require $path;
    }
}
