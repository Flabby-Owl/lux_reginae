<?php

declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function forum_start_session(): void
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

function forum_current_user(): ?array
{
    forum_start_session();
    $userId = (string) ($_SESSION['user_id'] ?? '');

    return $userId === '' ? null : forum_find_user_by_id(forum_load_users(), $userId);
}

function forum_is_logged_in(): bool
{
    return forum_current_user() !== null;
}

function forum_has_role(array $allowedRoles): bool
{
    $user = forum_current_user();

    return $user !== null && in_array((string) $user['role'], $allowedRoles, true);
}

function forum_require_login(): void
{
    if (!forum_is_logged_in()) {
        forum_redirect('./login.php');
    }
}

function forum_require_role(array $allowedRoles): void
{
    if (forum_has_role($allowedRoles)) {
        return;
    }

    http_response_code(403);
    forum_header('Acces refuse');
    ?>
    <div class="alert alert-warning">Vous n avez pas les droits necessaires.</div>
    <a class="btn btn-primary" href="./">Retour au forum</a>
    <?php
    forum_footer();
    exit;
}

function forum_csrf_token(): string
{
    forum_start_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function forum_csrf_field(): void
{
    ?>
    <input type="hidden" name="csrf_token" value="<?= forum_h(forum_csrf_token()) ?>">
    <?php
}

function forum_validate_csrf(): bool
{
    forum_start_session();

    return hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($_POST['csrf_token'] ?? ''));
}

function forum_login_user(array $user): void
{
    forum_start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

function forum_logout_user(): void
{
    forum_start_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}
