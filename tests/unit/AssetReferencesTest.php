<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$publicRoot = $root . '/public';
$documents = array_merge(
    glob($publicRoot . '/*.html') ?: [],
    glob($publicRoot . '/*.php') ?: [],
    glob($root . '/app/Views/*/*.php') ?: [],
);

foreach ($documents as $document) {
    $contents = file_get_contents($document);
    if ($contents === false) {
        throw new RuntimeException("Não foi possível ler {$document}");
    }

    preg_match_all('/(?:href|src)=["\']([^"\']+)["\']/', $contents, $matches);
    foreach ($matches[1] as $reference) {
        if (
            str_starts_with($reference, '#')
            || str_starts_with($reference, '//')
            || preg_match('/^(?:https?:|mailto:|data:)/', $reference) === 1
            || str_contains($reference, '<?')
        ) {
            continue;
        }

        $path = $publicRoot . '/' . ltrim((string) parse_url($reference, PHP_URL_PATH), '/');
        if (!is_file($path)) {
            throw new RuntimeException(sprintf(
                'Referência local ausente em %s: %s',
                basename($document),
                $reference,
            ));
        }
    }
}

echo "AssetReferencesTest: ok\n";
