<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeFiles = [
    'public/login.php',
    'public/eventos.php',
    'public/eventos2.php',
    'public/listar_evento.php',
    'public/cadastrar_evento.php',
    'public/editar_evento.php',
    'public/apagar_evento.php',
];

foreach ($routeFiles as $routeFile) {
    $contents = file_get_contents($root . '/' . $routeFile);
    if ($contents === false) {
        throw new RuntimeException("Rota ausente: {$routeFile}");
    }

    foreach (['SELECT ', 'INSERT ', 'UPDATE ', 'DELETE '] as $sqlVerb) {
        if (str_contains(strtoupper($contents), $sqlVerb)) {
            throw new RuntimeException("A rota {$routeFile} contém SQL; acesso a dados pertence ao Model.");
        }
    }
}

$allowedRootEntries = [
    '.dockerignore', '.env.example', '.git', '.gitattributes', '.github',
    '.gitignore', '.replit', 'Dockerfile', 'README.md', 'app', 'database',
    'docker', 'docker-compose.yml', 'docs', 'public', 'storage', 'tests',
];
$rootEntries = array_values(array_filter(scandir($root) ?: [], static fn (string $entry): bool => !in_array($entry, ['.', '..'], true)));
$unexpectedRootEntries = array_values(array_diff($rootEntries, $allowedRootEntries));
if ($unexpectedRootEntries !== []) {
    throw new RuntimeException('Arquivos inesperados na raiz: ' . implode(', ', $unexpectedRootEntries));
}

foreach (glob($root . '/app/Controllers/*.php') ?: [] as $controllerFile) {
    $contents = strtoupper((string) file_get_contents($controllerFile));
    foreach (['SELECT ', 'INSERT ', 'UPDATE ', 'DELETE '] as $sqlVerb) {
        if (str_contains($contents, $sqlVerb)) {
            throw new RuntimeException('Controller contém SQL: ' . basename($controllerFile));
        }
    }
}

foreach ([
    'app/Controllers/AuthController.php',
    'app/Controllers/DiagnosticsController.php',
    'app/Controllers/EventController.php',
    'app/Models/Event.php',
    'app/Models/SystemStatus.php',
    'app/Models/User.php',
    'app/Views/auth/login.php',
    'app/Views/events/manage.php',
] as $mvcFile) {
    if (!is_file($root . '/' . $mvcFile)) {
        throw new RuntimeException("Camada MVC ausente: {$mvcFile}");
    }
}

echo "ArchitectureTest: ok\n";
