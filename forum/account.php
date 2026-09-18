<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

forum_require_login();
$user = forum_current_user();

if (($_GET['export'] ?? '') === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="lux-reginae-compte.json"');
    echo json_encode([
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'created_at' => $user['created_at'],
        'last_login_at' => $user['last_login_at'],
        'privacy_accepted_at' => $user['privacy_accepted_at'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!forum_validate_csrf()) {
        $errors[] = 'Session expiree. Rechargez la page.';
    }

    if ($errors === []) {
        forum_update_users(static function (array &$users) use ($user): void {
            $users = array_values(array_filter($users, static fn (array $item): bool => $item['id'] !== $user['id']));
        });
        forum_update(static function (array &$forum) use ($user): void {
            foreach ($forum['topics'] as &$topic) {
                if (($topic['author_id'] ?? '') === $user['id']) {
                    $topic['author'] = 'Compte supprime';
                    $topic['author_id'] = null;
                }
            }
            foreach ($forum['replies'] as &$reply) {
                if (($reply['author_id'] ?? '') === $user['id']) {
                    $reply['author'] = 'Compte supprime';
                    $reply['author_id'] = null;
                }
            }
        });
        forum_logout_user();
        forum_redirect('./');
    }
}

forum_header('Mon compte');
forum_error_list($errors);
?>

<section class="forum-form">
  <h2><?= forum_h($user['username']) ?></h2>
  <p><strong>Role :</strong> <?= forum_h($user['role']) ?></p>
  <p><strong>Compte cree le :</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
  <p class="text-muted">Vos donnees sont limitees au fonctionnement du forum : pseudo, mot de passe hache, role et dates techniques.</p>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-primary" href="./account.php?export=json">Exporter mes donnees</a>
    <?php if ($user['role'] === 'Administrateur'): ?>
      <a class="btn btn-outline-primary" href="./admin.php">Administrer les roles</a>
    <?php endif; ?>
  </div>
</section>

<form class="forum-form mt-3" method="post" action="./account.php">
  <?php forum_csrf_field(); ?>
  <input type="hidden" name="action" value="delete">
  <h2>Supprimer mon compte</h2>
  <p class="text-muted">Le compte sera supprime. Les anciens messages seront conserves avec la mention "Compte supprime" afin de garder la coherence des discussions.</p>
  <button class="btn btn-outline-danger" type="submit">Supprimer mon compte</button>
</form>

<?php forum_footer(); ?>
