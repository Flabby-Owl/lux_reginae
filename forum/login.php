<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = forum_post_value('username');
    $password = (string) ($_POST['password'] ?? '');

    if (!forum_validate_csrf()) {
        $errors[] = 'Session expiree. Rechargez la page.';
    }

    $user = forum_find_user_by_username(forum_load_users(), $username);

    if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
        $errors[] = 'Identifiants incorrects.';
    }

    if ($errors === []) {
        forum_update_users(static function (array &$users) use ($user): void {
            foreach ($users as &$item) {
                if ($item['id'] === $user['id']) {
                    $item['last_login_at'] = date(DATE_ATOM);
                    break;
                }
            }
        });
        forum_login_user($user);
        forum_redirect('./account.php');
    }
}

forum_header('Connexion');
forum_error_list($errors);
?>

<form class="forum-form" method="post" action="./login.php">
  <?php forum_csrf_field(); ?>
  <div class="row g-3">
    <div class="col-12">
      <label class="form-label" for="username">Pseudo</label>
      <input class="form-control" id="username" name="username" required value="<?= forum_h($_POST['username'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label" for="password">Mot de passe</label>
      <input class="form-control" id="password" name="password" type="password" required>
    </div>
    <div class="col-12">
      <button class="btn btn-primary" type="submit">Se connecter</button>
      <a class="btn btn-outline-primary" href="./register.php">Creer un compte</a>
    </div>
  </div>
</form>

<?php forum_footer(); ?>
