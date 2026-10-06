<?php

declare(strict_types=1);

namespace Nuartemo\Domain;

use DateTimeImmutable;
use InvalidArgumentException;

final class EventValidator
{
    /** @return array{title: string, color: string, start: string, end: string} */
    public static function validate(array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $color = strtoupper(trim((string) ($input['color'] ?? '')));
        $start = self::date((string) ($input['start'] ?? ''), 'início');
        $end = self::date((string) ($input['end'] ?? ''), 'fim');

        if ($title === '' || mb_strlen($title) > 150) {
            throw new InvalidArgumentException('O título deve ter entre 1 e 150 caracteres.');
        }

        if (preg_match('/^#[0-9A-F]{6}$/', $color) !== 1) {
            throw new InvalidArgumentException('A cor do evento é inválida.');
        }

        if ($end < $start) {
            throw new InvalidArgumentException('O término não pode ser anterior ao início.');
        }

        return [
            'title' => $title,
            'color' => $color,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
        ];
    }

    private static function date(string $value, string $field): DateTimeImmutable
    {
        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s', DATE_ATOM] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date;
            }
        }

        throw new InvalidArgumentException("A data de {$field} é inválida.");
    }
}
