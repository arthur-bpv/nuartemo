<?php
declare(strict_types=1);
namespace Nuartemo\Models;

use Nuartemo\Models\Contracts\UserRepository;
use PDO;

final class User implements UserRepository
{
    public function __construct(private readonly PDO $database) {}

    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->prepare('SELECT id, nome, senha FROM usuarios WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $statement = $this->database->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
        $statement->execute(['senha' => $passwordHash, 'id' => $id]);
    }
}
