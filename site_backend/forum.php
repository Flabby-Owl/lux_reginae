<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../forum/includes/storage.php';

function lr_forum_payload(array $data): array
{
    $topics = $data['topics'] ?? [];
    usort($topics, fn ($a, $b) => strtotime((string) ($b['updated_at'] ?? $b['created_at'])) <=> strtotime((string) ($a['updated_at'] ?? $a['created_at'])));

    foreach ($topics as &$topic) {
        $topic['reply_count'] = forum_reply_count($data, (int) $topic['id']);
        $topic['latest_activity'] = forum_latest_activity($data, $topic);
    }
    unset($topic);

    return [
        'categories' => $data['categories'] ?? [],
        'topics' => $topics,
        'replies' => $data['replies'] ?? [],
        'csrf' => lr_csrf_token(),
        'auth' => lr_public_auth_context(),
    ];
}

function lr_forum_json_input(): array
{
    $payload = json_decode((string) file_get_contents('php://input'), true);
    return is_array($payload) ? $payload : [];
}

function lr_forum_check_csrf(array $payload): void
{
    if (!hash_equals(lr_csrf_token(), (string) ($payload['csrf_token'] ?? ''))) {
        lr_json_response(['error' => 'Session expiree. Recharge la page.'], 419);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    lr_json_response(lr_forum_payload(forum_load()));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lr_json_response(['error' => 'Method not allowed'], 405);
}

$payload = lr_forum_json_input();
lr_forum_check_csrf($payload);
$action = (string) ($payload['action'] ?? '');

if ($action === 'create_category') {
    lr_require_role(['Administrateur']);
    $name = trim((string) ($payload['name'] ?? ''));
    $description = trim((string) ($payload['description'] ?? ''));
    if ($name === '') {
        lr_json_response(['error' => 'Nom de catégorie obligatoire.'], 422);
    }
    forum_update(function (array &$forum) use ($name, $description): void {
        $forum['categories'][] = [
            'id' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . time(),
            'name' => $name,
            'description' => $description,
        ];
    });
    lr_json_response(lr_forum_payload(forum_load()));
}

if ($action === 'create_topic') {
    $user = lr_current_user();
    if ($user === null) {
        lr_json_response(['error' => 'Connexion requise.'], 401);
    }
    $categoryId = trim((string) ($payload['category_id'] ?? ''));
    $title = trim((string) ($payload['title'] ?? ''));
    $body = trim((string) ($payload['body'] ?? ''));
    $data = forum_load();
    if (!forum_category($data, $categoryId)) {
        lr_json_response(['error' => 'Categorie invalide.'], 422);
    }
    if (strlen($title) < 4 || strlen($body) < 3) {
        lr_json_response(['error' => 'Titre ou message trop court.'], 422);
    }
    forum_update(function (array &$forum) use ($user, $categoryId, $title, $body): void {
        $id = (int) $forum['next_topic_id']++;
        $now = date(DATE_ATOM);
        $forum['topics'][] = [
            'id' => $id,
            'category_id' => $categoryId,
            'title' => $title,
            'body' => $body,
            'author' => $user['username'],
            'author_id' => $user['id'],
            'created_at' => $now,
            'updated_at' => $now,
        ];
    });
    lr_json_response(lr_forum_payload(forum_load()));
}

if ($action === 'reply') {
    $user = lr_current_user();
    if ($user === null) {
        lr_json_response(['error' => 'Connexion requise.'], 401);
    }
    $topicId = (int) ($payload['topic_id'] ?? 0);
    $body = trim((string) ($payload['body'] ?? ''));
    if ($body === '' || !forum_topic(forum_load(), $topicId)) {
        lr_json_response(['error' => 'Reponse invalide.'], 422);
    }
    forum_update(function (array &$forum) use ($user, $topicId, $body): void {
        $forum['replies'][] = [
            'id' => (int) $forum['next_reply_id']++,
            'topic_id' => $topicId,
            'body' => $body,
            'author' => $user['username'],
            'author_id' => $user['id'],
            'created_at' => date(DATE_ATOM),
        ];
        foreach ($forum['topics'] as &$topic) {
            if ((int) $topic['id'] === $topicId) {
                $topic['updated_at'] = date(DATE_ATOM);
            }
        }
        unset($topic);
    });
    lr_json_response(lr_forum_payload(forum_load()));
}

if ($action === 'delete_topic') {
    lr_require_role(['Administrateur', 'Modérateur']);
    $topicId = (int) ($payload['topic_id'] ?? 0);
    forum_update(function (array &$forum) use ($topicId): void {
        $forum['topics'] = array_values(array_filter($forum['topics'], fn ($topic) => (int) $topic['id'] !== $topicId));
        $forum['replies'] = array_values(array_filter($forum['replies'], fn ($reply) => (int) $reply['topic_id'] !== $topicId));
    });
    lr_json_response(lr_forum_payload(forum_load()));
}

if ($action === 'delete_reply') {
    lr_require_role(['Administrateur', 'Modérateur']);
    $replyId = (int) ($payload['reply_id'] ?? 0);
    forum_update(function (array &$forum) use ($replyId): void {
        $forum['replies'] = array_values(array_filter($forum['replies'], fn ($reply) => (int) $reply['id'] !== $replyId));
    });
    lr_json_response(lr_forum_payload(forum_load()));
}

lr_json_response(['error' => 'Action inconnue.'], 400);
