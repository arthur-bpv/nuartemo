<?php
declare(strict_types=1);
namespace Nuartemo\Controllers;

use Nuartemo\Domain\EventValidator;
use Nuartemo\Models\Contracts\EventRepository;

final class EventController
{
    public function __construct(private readonly EventRepository $events) {}
    public function index(): array { return $this->events->all(); }

    public function create(array $input): array
    {
        $event = EventValidator::validate($input);
        return ['id' => $this->events->create($event)] + $event;
    }

    public function update(int $id, array $input): array
    {
        $event = EventValidator::validate($input);
        $this->events->update($id, $event);
        return ['id' => $id] + $event;
    }

    public function delete(int $id): void { $this->events->delete($id); }
}
