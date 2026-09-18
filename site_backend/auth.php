<?php

declare(strict_types=1);

const LR_USERS_FILE = __DIR__ . '/data/users.json';
const LR_ROLES = ['Administrateur', 'Modérateur', 'Utilisateur'];

function lr_bootstrap_users(): void
{
    $dataDir = dirname(LR_USERS_FILE);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }
    if (!file_exists(LR_USERS_FILE)) {
        lr_save_users([]);
    }
}

function lr_load_users(): array
{
    lr_bootstrap_users();
    $users = json_decode((string) file_get_contents(LR_USERS_FILE), true);
    return is_array($users) ? $users : [];
}

function lr_save_users(array $users): void
{
    file_put_contents(LR_USERS_FILE, json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function lr_update_users(callable $callback): mixed
{
    lr_bootstrap_users();
    $handle = fopen(LR_USERS_FILE, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Impossible d ouvrir le stockage des comptes.');
    }
    flock($handle, LOCK_EX);
    rewind($handle);
    $users = json_decode((string) stream_get_contents($handle), true);
    if (!is_array($users)) {
        $users = [];
    }
    $result = $callback($users);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $result;
}

function lr_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

function lr_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function lr_post(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function lr_find_user_by_id(string $id): ?array
{
    foreach (lr_load_users() as $user) {
        if (($user['id'] ?? '') === $id) {
            return $user;
        }
    }
    return null;
}

function lr_find_user_by_username(string $username): ?array
{
    foreach (lr_load_users() as $user) {
        if (strcasecmp((string) ($user['username'] ?? ''), $username) === 0) {
            return $user;
        }
    }
    return null;
}

function lr_current_user(): ?array
{
    lr_start_session();
    $id = (string) ($_SESSION['user_id'] ?? '');
    return $id === '' ? null : lr_find_user_by_id($id);
}

function lr_has_role(array $roles): bool
{
    $user = lr_current_user();
    return $user !== null && in_array((string) $user['role'], $roles, true);
}

function lr_require_login(): void
{
    if (lr_current_user() === null) {
        header('Location: /login.php');
        exit;
    }
}

function lr_require_role(array $roles): void
{
    if (!lr_has_role($roles)) {
        http_response_code(403);
        echo 'Acces refuse';
        exit;
    }
}

function lr_login(array $user): void
{
    lr_start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

function lr_logout(): void
{
    lr_start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function lr_csrf_token(): string
{
    lr_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function lr_csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . lr_h(lr_csrf_token()) . '">';
}

function lr_validate_csrf(): bool
{
    lr_start_session();
    return hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($_POST['csrf_token'] ?? ''));
}

function lr_json_response(mixed $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function lr_public_auth_context(): array
{
    $user = lr_current_user();
    return [
        'user' => $user ? [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ] : null,
        'permissions' => [
            'editSite' => lr_has_role(['Administrateur']),
            'editWiki' => lr_has_role(['Administrateur', 'Modérateur', 'Utilisateur']),
            'manageEvents' => lr_has_role(['Administrateur', 'Modérateur']),
            'createForumTopic' => lr_has_role(['Administrateur', 'Modérateur', 'Utilisateur']),
            'moderateForum' => lr_has_role(['Administrateur', 'Modérateur']),
            'manageAccounts' => lr_has_role(['Administrateur']),
        ],
    ];
}
