<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = forum_post_value('username');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $privacyConsent = isset($_POST['privacy_consent']);
    $website = forum_post_value('website');

    if (!forum_validate_csrf()) {
        $errors[] = 'Session expiree. Rechargez la page.';
    }

    if ($website !== '') {
        $errors[] = 'Le formulaire semble invalide.';
    }

    if (!preg_match('/^[A-Za-z0-9 _\'-]{3,40}$/', $username)) {
        $errors[] = 'Le pseudo doit faire entre 3 et 40 caracteres et rester lisible.';
    }

    if (strlen($password) < 10) {
        $errors[] = 'Le mot de passe doit contenir au moins 10 caracteres.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (!$privacyConsent) {
        $errors[] = 'Vous devez accepter la politique de confidentialite pour creer un compte.';
    }

    if (forum_find_user_by_username(forum_load_users(), $username) !== null) {
        $errors[] = 'Ce pseudo est deja utilise.';
    }

    if ($errors === []) {
        [, $user] = forum_update_users(static function (array &$users) use ($username, $password): array {
            $now = date(DATE_ATOM);
            $user = [
                'id' => bin2hex(random_bytes(16)),
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'Utilisateur',
                'created_at' => $now,
                'last_login_at' => null,
                'privacy_accepted_at' => $now,
            ];
            $users[] = $user;

            return $user;
        });

        forum_login_user($user);
        forum_redirect('./account.php');
    }
}

forum_header('Creer un compte');
forum_error_list($errors);
?>

<form class="forum-form" method="post" action="./register.php">
  <?php forum_csrf_field(); ?>
  <div class="row g-3">
    <div class="col-12">
      <label class="form-label" for="username">Pseudo</label>
      <input class="form-control" id="username" name="username" maxlength="40" required value="<?= forum_h($_POST['username'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label" for="password">Mot de passe</label>
      <input class="form-control" id="password" name="password" type="password" minlength="10" required>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="password_confirm">Confirmation</label>
      <input class="form-control" id="password_confirm" name="password_confirm" type="password" minlength="10" required>
    </div>
    <div class="col-12">
      <div class="form-check">
        <input class="form-check-input" id="privacy_consent" name="privacy_consent" type="checkbox" required>
        <label class="form-check-label" for="privacy_consent">
          J accepte la <a href="./privacy.php" target="_blank">politique de confidentialite</a>.
        </label>
      </div>
      <p class="text-muted mt-2 mb-0">Donnees collectees au minimum : pseudo, mot de passe hache, role, dates techniques du compte.</p>
    </div>
    <div class="d-none">
      <label for="website">Site web</label>
      <input id="website" name="website" tabindex="-1" autocomplete="off">
    </div>
    <div class="col-12">
      <button class="btn btn-primary" type="submit">Creer mon compte</button>
      <a class="btn btn-outline-primary" href="./login.php">J ai deja un compte</a>
    </div>
  </div>
</form>

<?php forum_footer(); ?>
