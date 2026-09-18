<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

forum_require_role(['Administrateur']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!forum_validate_csrf()) {
        $errors[] = 'Session expiree. Rechargez la page.';
    }

    $userId = forum_post_value('user_id');
    $role = forum_post_value('role');

    if (!in_array($role, FORUM_ROLES, true)) {
        $errors[] = 'Role invalide.';
    }

    if ($errors === []) {
        forum_update_users(static function (array &$users) use ($userId, $role): void {
            foreach ($users as &$user) {
                if ($user['id'] === $userId) {
                    $user['role'] = $role;
                    break;
                }
            }
        });
    }
}

$users = forum_load_users();
forum_header('Administration');
forum_error_list($errors);
?>

<section class="forum-form">
  <h2>Gestion des roles</h2>
  <p class="text-muted">Les comptes crees par inscription restent toujours Utilisateur par defaut. Seul un Administrateur peut changer un role.</p>
  <?php foreach ($users as $user): ?>
    <form class="role-row" method="post" action="./admin.php">
      <?php forum_csrf_field(); ?>
      <input type="hidden" name="user_id" value="<?= forum_h($user['id']) ?>">
      <strong><?= forum_h($user['username']) ?></strong>
      <select class="form-select" name="role" aria-label="Role de <?= forum_h($user['username']) ?>">
        <?php foreach (FORUM_ROLES as $role): ?>
          <option value="<?= forum_h($role) ?>" <?= $user['role'] === $role ? 'selected' : '' ?>><?= forum_h($role) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary" type="submit">Enregistrer</button>
    </form>
  <?php endforeach; ?>
</section>

<?php forum_footer(); ?>
