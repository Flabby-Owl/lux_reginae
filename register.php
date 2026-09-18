<?php
require_once __DIR__ . '/site_backend/auth.php';
lr_start_session();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = lr_post('username');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');
    if (!lr_validate_csrf()) $errors[] = 'Session expirée.';
    if (!preg_match('/^[A-Za-z0-9 _\'-]{3,40}$/', $username)) $errors[] = 'Pseudo invalide.';
    if (strlen($password) < 10) $errors[] = 'Mot de passe trop court, 10 caractères minimum.';
    if ($password !== $confirm) $errors[] = 'Les mots de passe ne correspondent pas.';
    if (!isset($_POST['privacy_consent'])) $errors[] = 'La politique de confidentialité doit être acceptée.';
    if (lr_find_user_by_username($username)) $errors[] = 'Ce pseudo existe déjà.';
    if (!$errors) {
        $user = lr_update_users(function (array &$users) use ($username, $password): array {
            $now = date(DATE_ATOM);
            $user = ['id' => bin2hex(random_bytes(16)), 'username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'Utilisateur', 'created_at' => $now, 'last_login_at' => null, 'privacy_accepted_at' => $now];
            $users[] = $user;
            return $user;
        });
        lr_login($user);
        header('Location: ./account.php'); exit;
    }
}
$title = 'Inscription';
include __DIR__ . '/site_backend/page_header.php';
?>
<form class="auth-card" method="post">
  <?php lr_csrf_field(); ?>
  <h1>Créer un compte</h1>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= lr_h($error) ?></div><?php endforeach; ?>
  <label>Pseudo <input class="form-control" name="username" required value="<?= lr_h($_POST['username'] ?? '') ?>"></label>
  <label>Mot de passe <input class="form-control" type="password" name="password" minlength="10" required></label>
  <label>Confirmation <input class="form-control" type="password" name="password_confirm" minlength="10" required></label>
  <label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="privacy_consent" required> J'accepte la <a href="./privacy.php">politique de confidentialité</a>.</label>
  <button class="btn btn-primary mt-3" type="submit">Créer mon compte</button>
</form>
<?php include __DIR__ . '/site_backend/page_footer.php'; ?>
