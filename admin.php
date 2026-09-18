<?php
require_once __DIR__ . '/site_backend/auth.php';
lr_require_role(['Administrateur']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && lr_validate_csrf()) {
    $action = lr_post('action');
    $id = lr_post('user_id');
    if ($action === 'role' && in_array(lr_post('role'), LR_ROLES, true)) {
        lr_update_users(function (array &$users) use ($id): void { foreach ($users as &$user) if ($user['id'] === $id) $user['role'] = lr_post('role'); });
    }
    if ($action === 'delete') {
        lr_update_users(function (array &$users) use ($id): void { $users = array_values(array_filter($users, fn ($u) => $u['id'] !== $id)); });
    }
    header('Location: ./admin.php'); exit;
}
$title = 'Gestion globale';
include __DIR__ . '/site_backend/page_header.php';
?>
<section class="auth-card wide">
  <h1>Gestion globale des comptes</h1>
  <?php foreach (lr_load_users() as $user): ?>
    <form class="admin-row" method="post">
      <?php lr_csrf_field(); ?>
      <input type="hidden" name="user_id" value="<?= lr_h($user['id']) ?>">
      <strong><?= lr_h($user['username']) ?></strong>
      <select class="form-select" name="role"><?php foreach (LR_ROLES as $role): ?><option <?= $role === $user['role'] ? 'selected' : '' ?>><?= lr_h($role) ?></option><?php endforeach; ?></select>
      <button class="btn btn-primary" name="action" value="role">Modifier</button>
      <button class="btn btn-outline-danger" name="action" value="delete">Supprimer</button>
    </form>
  <?php endforeach; ?>
</section>
<?php include __DIR__ . '/site_backend/page_footer.php'; ?>
