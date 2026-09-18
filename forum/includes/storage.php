<?php
declare(strict_types=1);
const LR_FORUM_FILE = __DIR__ . '/../data/forum.json';

function forum_seed(): array
{
    return ['next_topic_id' => 1, 'next_reply_id' => 1, 'categories' => [
        ['id' => 'annonces', 'name' => 'Annonces', 'description' => 'Informations importantes de la compagnie libre.'],
        ['id' => 'sorties', 'name' => 'Sorties', 'description' => 'Organisation des raids, cartes et activités.'],
        ['id' => 'aide', 'name' => 'Aide et conseils', 'description' => 'Questions, guides et progression.'],
    ], 'topics' => [], 'replies' => []];
}

function forum_load(): array
{
    if (!is_dir(dirname(LR_FORUM_FILE))) mkdir(dirname(LR_FORUM_FILE), 0775, true);
    if (!file_exists(LR_FORUM_FILE)) forum_save(forum_seed());
    $data = json_decode((string) file_get_contents(LR_FORUM_FILE), true);
    return is_array($data) ? $data : forum_seed();
}

function forum_save(array $data): void
{
    file_put_contents(LR_FORUM_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function forum_update(callable $callback): mixed
{
    $data = forum_load();
    $result = $callback($data);
    forum_save($data);
    return $result;
}

function forum_category(array $data, string $id): ?array
{
    foreach ($data['categories'] as $category) if ($category['id'] === $id) return $category;
    return null;
}

function forum_topic(array $data, int $id): ?array
{
    foreach ($data['topics'] as $topic) if ((int) $topic['id'] === $id) return $topic;
    return null;
}

function forum_topics(array $data, string $categoryId): array
{
    $topics = array_values(array_filter($data['topics'], fn ($topic) => $topic['category_id'] === $categoryId));
    usort($topics, fn ($a, $b) => strtotime((string) ($b['updated_at'] ?? $b['created_at'])) <=> strtotime((string) ($a['updated_at'] ?? $a['created_at'])));
    return $topics;
}

function forum_replies(array $data, int $topicId): array
{
    return array_values(array_filter($data['replies'], fn ($reply) => (int) $reply['topic_id'] === $topicId));
}

function forum_reply_count(array $data, int $topicId): int
{
    return count(forum_replies($data, $topicId));
}

function forum_latest_activity(array $data, array $topic): string
{
    $latest = $topic['updated_at'] ?? $topic['created_at'] ?? '';
    foreach (forum_replies($data, (int) $topic['id']) as $reply) {
        if (strtotime((string) $reply['created_at']) > strtotime((string) $latest)) {
            $latest = (string) $reply['created_at'];
        }
    }
    return $latest ? date('d/m/Y H:i', strtotime($latest)) : 'Aucune activité';
}
