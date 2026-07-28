<?php

declare(strict_types=1);

const FORUM_DATA_FILE = __DIR__ . '/../data/forum.json';

function forum_bootstrap(): void
{
    $dataDir = dirname(FORUM_DATA_FILE);

    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    if (!file_exists(FORUM_DATA_FILE)) {
        forum_save([
            'next_topic_id' => 1,
            'next_reply_id' => 1,
            'categories' => [
                ['id' => 'annonces', 'name' => 'Annonces', 'description' => 'Informations importantes de la compagnie libre.'],
                ['id' => 'sorties', 'name' => 'Sorties', 'description' => 'Organisation des raids, cartes aux tresors et activites.'],
                ['id' => 'aide', 'name' => 'Aide et conseils', 'description' => 'Questions, guides, craft, jobs et progression.'],
                ['id' => 'taverne', 'name' => 'La taverne', 'description' => 'Discussions libres entre membres.'],
            ],
            'topics' => [],
            'replies' => [],
        ]);
    }
}

function forum_load(): array
{
    forum_bootstrap();
    $json = file_get_contents(FORUM_DATA_FILE);
    $data = json_decode((string) $json, true);

    if (!is_array($data)) {
        return [
            'next_topic_id' => 1,
            'next_reply_id' => 1,
            'categories' => [],
            'topics' => [],
            'replies' => [],
        ];
    }

    return $data;
}

function forum_save(array $data): void
{
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($encoded === false) {
        throw new RuntimeException('Impossible de preparer les donnees du forum.');
    }

    file_put_contents(FORUM_DATA_FILE, $encoded, LOCK_EX);
}

function forum_update(callable $callback): array
{
    forum_bootstrap();
    $handle = fopen(FORUM_DATA_FILE, 'c+');

    if ($handle === false) {
        throw new RuntimeException('Impossible d ouvrir le stockage du forum.');
    }

    flock($handle, LOCK_EX);
    rewind($handle);
    $json = stream_get_contents($handle);
    $data = json_decode((string) $json, true);

    if (!is_array($data)) {
        $data = forum_load();
    }

    $result = $callback($data);
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($encoded === false) {
        flock($handle, LOCK_UN);
        fclose($handle);
        throw new RuntimeException('Impossible de preparer les donnees du forum.');
    }

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, $encoded);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return [$data, $result];
}

function forum_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function forum_excerpt(string $value, int $length = 160): string
{
    $clean = trim(preg_replace('/\s+/', ' ', $value) ?? '');

    if (forum_length($clean) <= $length) {
        return $clean;
    }

    return forum_slice($clean, 0, $length - 1) . '...';
}

function forum_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function forum_slice(string $value, int $start, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, $start, $length) : substr($value, $start, $length);
}

function forum_find_category(array $data, string $categoryId): ?array
{
    foreach ($data['categories'] as $category) {
        if ($category['id'] === $categoryId) {
            return $category;
        }
    }

    return null;
}

function forum_find_topic(array $data, int $topicId): ?array
{
    foreach ($data['topics'] as $topic) {
        if ((int) $topic['id'] === $topicId) {
            return $topic;
        }
    }

    return null;
}

function forum_replies_for_topic(array $data, int $topicId): array
{
    return array_values(array_filter($data['replies'], static fn (array $reply): bool => (int) $reply['topic_id'] === $topicId));
}

function forum_topics_for_category(array $data, string $categoryId): array
{
    $topics = array_values(array_filter($data['topics'], static fn (array $topic): bool => $topic['category_id'] === $categoryId));

    usort($topics, static fn (array $a, array $b): int => strcmp($b['updated_at'], $a['updated_at']));

    return $topics;
}

function forum_post_value(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function forum_redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}
