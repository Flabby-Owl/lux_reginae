<?php
require_once __DIR__ . '/site_backend/auth.php';
lr_start_session();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = lr_find_user_by_username(lr_post('username'));
    if (!lr_validate_csrf()) $errors[] = 'Session expirée.';
    if (!$user || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) $errors[] = 'Identifiants incorrects.';
    if (!$errors) {
        lr_update_users(function (array &$users) use ($user): void { foreach ($users as &$item) if ($item['id'] === $user['id']) $item['last_login_at'] = date(DATE_ATOM); });
        lr_login($user);
        header('Location: ./index.php'); exit;
    }
}
$title = 'Connexion';
include __DIR__ . '/site_backend/page_header.php';
?>
<form class="auth-card" method="post">
  <?php lr_csrf_field(); ?>
  <h1>Connexion</h1>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= lr_h($error) ?></div><?php endforeach; ?>
  <label>Pseudo <input class="form-control" name="username" required></label>
  <label>Mot de passe <input class="form-control" type="password" name="password" required></label>
  <button class="btn btn-primary mt-3" type="submit">Se connecter</button>
  <a class="btn btn-outline-primary mt-3" href="./register.php">Créer un compte</a>
</form>
<?php include __DIR__ . '/site_backend/page_footer.php'; ?>
