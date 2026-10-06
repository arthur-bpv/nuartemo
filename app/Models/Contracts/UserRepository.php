<?php
declare(strict_types=1);
namespace Nuartemo\Models\Contracts;

interface UserRepository
{
    public function findByEmail(string $email): ?array;
    public function updatePassword(int $id, string $passwordHash): void;
}
