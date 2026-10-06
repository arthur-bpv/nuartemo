<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Domain/EventValidator.php';

use Nuartemo\Domain\EventValidator;

$valid = EventValidator::validate([
    'title' => 'Sarau de poesia',
    'color' => '#123abc',
    'start' => '2026-10-01 19:00',
    'end' => '2026-10-01 21:00',
]);

assertSameValue('Sarau de poesia', $valid['title'], 'Deve aceitar título válido.');
assertSameValue('#123ABC', $valid['color'], 'Deve normalizar a cor.');

$htmlDate = EventValidator::validate([
    'title' => 'Oficina',
    'color' => '#123456',
    'start' => '2026-10-01T19:00',
    'end' => '2026-10-01T21:00',
]);
assertSameValue('2026-10-01 19:00:00', $htmlDate['start'], 'Deve aceitar o formato de datetime-local do formulário.');

foreach ([
    ['title' => '', 'color' => '#123456', 'start' => '2026-10-01 19:00', 'end' => '2026-10-01 21:00'],
    ['title' => 'Teste', 'color' => 'red', 'start' => '2026-10-01 19:00', 'end' => '2026-10-01 21:00'],
    ['title' => 'Teste', 'color' => '#123456', 'start' => 'invalida', 'end' => '2026-10-01 21:00'],
    ['title' => 'Teste', 'color' => '#123456', 'start' => '2026-10-01 21:00', 'end' => '2026-10-01 19:00'],
] as $invalid) {
    try {
        EventValidator::validate($invalid);
        throw new RuntimeException('Payload inválido foi aceito.');
    } catch (InvalidArgumentException) {
        // Esperado.
    }
}

echo "EventValidatorTest: ok\n";
