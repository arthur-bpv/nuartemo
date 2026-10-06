<?php
declare(strict_types=1);
namespace Nuartemo\Models\Contracts;

interface EventRepository
{
    public function all(): array;
    public function create(array $event): int;
    public function update(int $id, array $event): void;
    public function delete(int $id): void;
}
