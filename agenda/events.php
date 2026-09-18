<?php

declare(strict_types=1);

const AGENDA_DATA_FILE = __DIR__ . '/data/events.json';

header('Content-Type: application/json; charset=utf-8');

function agenda_seed(): array
{
    return [
        [
            'id' => 'seed-1',
            'title' => 'Soiree hebdomadaire',
            'date' => '2026-08-02',
            'time' => '20:45',
            'type' => 'Raid',
            'description' => 'Rendez-vous pour les grosses activites de la compagnie libre. Vocal recommande pour les calls.',
        ],
        [
            'id' => 'seed-2',
            'title' => 'Cartes aux tresors',
            'date' => '2026-08-05',
            'time' => '21:00',
            'type' => 'Carte',
            'description' => 'Sortie detente en groupe, cartes et coffres au programme.',
        ],
        [
            'id' => 'seed-3',
            'title' => 'Open-mic au Sourcil',
            'date' => '2026-08-09',
            'time' => '21:30',
            'type' => 'Social',
            'description' => 'Une soiree libre pour profiter de la scene, ecouter les bardes et passer au bar.',
        ],
    ];
}

function agenda_bootstrap(): void
{
    $dataDir = dirname(AGENDA_DATA_FILE);

    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    if (!file_exists(AGENDA_DATA_FILE)) {
        agenda_save(agenda_seed());
    }
}

function agenda_load(): array
{
    agenda_bootstrap();
    $json = file_get_contents(AGENDA_DATA_FILE);
    $events = json_decode((string) $json, true);

    return is_array($events) ? $events : agenda_seed();
}

function agenda_save(array $events): void
{
    $encoded = json_encode(array_values($events), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($encoded === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Storage error']);
        exit;
    }

    file_put_contents(AGENDA_DATA_FILE, $encoded, LOCK_EX);
}

function agenda_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function agenda_slice(string $value, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
}

function agenda_clean_event(array $event): array
{
    $types = ['Raid', 'Carte', 'Social', 'Autre'];
    $type = in_array((string) ($event['type'] ?? ''), $types, true) ? (string) $event['type'] : 'Autre';
    $id = trim((string) ($event['id'] ?? 'event-' . time()));
    $title = trim((string) ($event['title'] ?? ''));
    $description = trim((string) ($event['description'] ?? ''));

    return [
        'id' => $id,
        'title' => agenda_length($title) > 90 ? agenda_slice($title, 90) : $title,
        'date' => trim((string) ($event['date'] ?? '')),
        'time' => trim((string) ($event['time'] ?? '')),
        'type' => $type,
        'description' => agenda_length($description) > 1200 ? agenda_slice($description, 1200) : $description,
    ];
}

function agenda_is_valid(array $event): bool
{
    return $event['id'] !== ''
        && $event['title'] !== ''
        && preg_match('/^\d{4}-\d{2}-\d{2}$/', $event['date']) === 1
        && preg_match('/^\d{2}:\d{2}$/', $event['time']) === 1
        && $event['description'] !== '';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(agenda_load(), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$action = (string) ($payload['action'] ?? '');
$event = isset($payload['event']) && is_array($payload['event']) ? agenda_clean_event($payload['event']) : [];
$events = agenda_load();

if ($action === 'delete' && isset($event['id'])) {
    agenda_save(array_filter($events, static fn (array $item): bool => $item['id'] !== $event['id']));
    echo json_encode(agenda_load(), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($action === 'create' || $action === 'update') && agenda_is_valid($event)) {
    $found = false;

    foreach ($events as &$item) {
        if ($item['id'] === $event['id']) {
            $item = $event;
            $found = true;
            break;
        }
    }

    unset($item);

    if (!$found) {
        $events[] = $event;
    }

    agenda_save($events);
    echo json_encode(agenda_load(), JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(422);
echo json_encode(['error' => 'Invalid event']);
