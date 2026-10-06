<?php
declare(strict_types=1);
namespace Nuartemo\Controllers;

use Nuartemo\Models\Contracts\UserRepository;

final class AuthController
{
    public function __construct(private readonly UserRepository $users) {}

    public function authenticate(string $email, string $password): ?array
    {
        $normalizedEmail = filter_var(strtolower(trim($email)), FILTER_VALIDATE_EMAIL);
        if ($normalizedEmail === false || $password === '') return null;

        $user = $this->users->findByEmail($normalizedEmail);
        if ($user === null) return null;

        $storedPassword = (string) $user['senha'];
        $hasModernHash = password_get_info($storedPassword)['algo'] !== null;
        $valid = $hasModernHash ? password_verify($password, $storedPassword) : hash_equals($storedPassword, $password);
        if (!$valid) return null;

        if (!$hasModernHash) {
            $this->users->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        return ['id' => (int) $user['id'], 'nome' => (string) $user['nome']];
    }
}
