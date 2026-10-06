<?php
declare(strict_types=1);
namespace Nuartemo\Models;

use Nuartemo\Models\Contracts\EventRepository;
use PDO;

final class Event implements EventRepository
{
    public function __construct(private readonly PDO $database) {}

    public function all(): array
    {
        return $this->database->query('SELECT id, title, color, start, end FROM events ORDER BY start ASC')->fetchAll();
    }

    public function create(array $event): int
    {
        $statement = $this->database->prepare('INSERT INTO events (title, color, start, end) VALUES (:title, :color, :start, :end)');
        $statement->execute($event);
        return (int) $this->database->lastInsertId();
    }

    public function update(int $id, array $event): void
    {
        $statement = $this->database->prepare('UPDATE events SET title = :title, color = :color, start = :start, end = :end WHERE id = :id');
        $statement->execute($event + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM events WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
