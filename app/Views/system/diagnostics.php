<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Diagnóstico técnico</title></head>
<body>
    <h1>Diagnóstico técnico</h1>
    <p>Disponível somente no ambiente de desenvolvimento autenticado.</p>
    <ul>
        <li>PHP: <?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></li>
        <li>SAPI: <?= htmlspecialchars(PHP_SAPI, ENT_QUOTES, 'UTF-8') ?></li>
        <li>Banco: <?= $databaseStatus ?></li>
        <li>PDO drivers: <?= htmlspecialchars(implode(', ', PDO::getAvailableDrivers()), ENT_QUOTES, 'UTF-8') ?></li>
    </ul>
    <h2>Extensões necessárias</h2>
    <ul><?php foreach ($extensions as $extension): ?><li><?= $extension ?>: <?= extension_loaded($extension) ? 'ativa' : 'ausente' ?></li><?php endforeach; ?></ul>
    <h2>Módulos Apache relevantes</h2>
    <ul><?php foreach (['rewrite_module', 'headers_module'] as $module): ?><li><?= $module ?>: <?= in_array($module, $apacheModules, true) ? 'ativo' : 'ausente' ?></li><?php endforeach; ?></ul>
</body>
</html>
