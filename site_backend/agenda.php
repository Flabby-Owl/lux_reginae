<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

const LR_EVENTS_FILE = __DIR__ . '/data/events.json';
const LR_EVENT_TYPES = ['Cartes', 'Sadiques', 'Raid 24', 'Farm monture', 'Donjons à embranchement', 'Défis Extrêmes', 'Irréel', 'Fun', 'Social', 'Autre'];

function lr_seed_events(): array
{
    return [
        ['id' => 'seed-1', 'title' => 'Soirée hebdomadaire', 'date' => '2026-08-02', 'time' => '20:45', 'type' => 'Sadiques', 'description' => 'Rendez-vous pour les grosses activités de la compagnie libre.'],
        ['id' => 'seed-2', 'title' => 'Cartes aux trésors', 'date' => '2026-08-05', 'time' => '21:00', 'type' => 'Cartes', 'description' => 'Sortie détente en groupe.'],
    ];
}

function lr_load_events(): array
{
    if (!file_exists(LR_EVENTS_FILE)) {
        file_put_contents(LR_EVENTS_FILE, json_encode(lr_seed_events(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    $events = json_decode((string) file_get_contents(LR_EVENTS_FILE), true);
    return is_array($events) ? $events : lr_seed_events();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    lr_json_response(['events' => lr_load_events(), 'auth' => lr_public_auth_context()]);
}

lr_require_role(['Administrateur', 'Modérateur']);
$payload = json_decode((string) file_get_contents('php://input'), true);
$events = lr_load_events();
$action = (string) ($payload['action'] ?? '');
$event = is_array($payload['event'] ?? null) ? $payload['event'] : [];

if ($action === 'delete') {
    $events = array_values(array_filter($events, static fn (array $item): bool => $item['id'] !== ($event['id'] ?? '')));
} elseif ($action === 'create' || $action === 'update') {
    $clean = [
        'id' => trim((string) ($event['id'] ?? 'event-' . time())),
        'title' => trim((string) ($event['title'] ?? '')),
        'date' => trim((string) ($event['date'] ?? '')),
        'time' => trim((string) ($event['time'] ?? '')),
        'type' => in_array(($event['type'] ?? ''), LR_EVENT_TYPES, true) ? $event['type'] : 'Autre',
        'description' => trim((string) ($event['description'] ?? '')),
    ];
    $found = false;
    foreach ($events as &$item) {
        if ($item['id'] === $clean['id']) {
            $item = $clean;
            $found = true;
            break;
        }
    }
    unset($item);
    if (!$found) {
        $events[] = $clean;
    }
}

file_put_contents(LR_EVENTS_FILE, json_encode(array_values($events), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
lr_json_response(['events' => lr_load_events(), 'auth' => lr_public_auth_context()]);
