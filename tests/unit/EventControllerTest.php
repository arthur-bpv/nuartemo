<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Support/bootstrap.php';

use Nuartemo\Controllers\EventController;
use Nuartemo\Models\Contracts\EventRepository;

$repository = new class implements EventRepository {
    public array $events = [];
    public ?int $updatedId = null;
    public ?int $deletedId = null;

    public function all(): array
    {
        return $this->events;
    }

    public function create(array $event): int
    {
        $this->events[] = ['id' => 17] + $event;
        return 17;
    }

    public function update(int $id, array $event): void
    {
        $this->updatedId = $id;
    }

    public function delete(int $id): void
    {
        $this->deletedId = $id;
    }
};

$controller = new EventController($repository);
$created = $controller->create([
    'title' => '  Sarau  ',
    'color' => '#123abc',
    'start' => '2026-10-01 19:00',
    'end' => '2026-10-01 21:00',
]);

assertSameValue(17, $created['id'], 'Controller deve devolver o identificador criado.');
assertSameValue('Sarau', $created['title'], 'Controller deve devolver dados validados.');
assertSameValue('#123ABC', $created['color'], 'Controller deve normalizar a cor.');
assertSameValue($repository->events, $controller->index(), 'Controller deve listar via model.');

$controller->update(17, $created);
$controller->delete(17);
assertSameValue(17, $repository->updatedId, 'Controller deve encaminhar atualização ao model.');
assertSameValue(17, $repository->deletedId, 'Controller deve encaminhar remoção ao model.');

echo "EventControllerTest: ok\n";
