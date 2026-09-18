<?php
require_once __DIR__ . '/../../site_backend/auth.php';
require_once __DIR__ . '/storage.php';

function forum_header(string $title): void
{
    $user = lr_current_user();
    ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= lr_h($title) ?> - Forum Lux Reginae</title>
  <link rel="icon" type="image/png" href="../site_img/logo_lxr.png">
  <link rel="apple-touch-icon" href="../site_img/logo_lxr.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../site_css/theme.css">
  <link rel="stylesheet" href="../site_css/admin.css">
  <link rel="stylesheet" href="./forum.css">
</head>
<body class="forum-body">
  <nav class="navbar navbar-dark site-nav">
    <div class="container forum-nav">
      <a class="navbar-brand" href="../index.php"><img class="brand-mark" src="../site_img/logo_lxr.png" alt=""> Lux Reginae</a>
      <div class="forum-nav__actions">
        <a class="btn btn-outline-light btn-sm" href="../index.php#space">Retour au site</a>
        <?php if ($user): ?>
          <a class="btn btn-outline-light btn-sm" href="../account.php"><?= lr_h($user['username']) ?> · <?= lr_h($user['role']) ?></a>
          <a class="btn btn-outline-light btn-sm" href="../logout.php">Déconnexion</a>
        <?php else: ?>
          <a class="btn btn-outline-light btn-sm" href="../login.php">Connexion</a>
          <a class="btn btn-primary btn-sm" href="../register.php">Inscription</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
  <header class="forum-hero">
    <div class="container forum-hero__inner">
      <img class="forum-hero__logo" src="../site_img/logo_lxr.png" alt="">
      <div>
        <p class="eyebrow">Forum communautaire</p>
        <h1><?= lr_h($title) ?></h1>
        <p>Organiser les sorties, poser une question, suivre les annonces et garder une trace propre des discussions.</p>
      </div>
    </div>
  </header>
  <main class="forum-main">
    <div class="container forum-shell">
<?php
}

function forum_footer(): void
{
    ?>
    </div>
  </main>
</body>
</html>
<?php
}

function forum_errors(array $errors): void
{
    foreach ($errors as $error) {
        echo '<div class="alert alert-danger">' . lr_h($error) . '</div>';
    }
}
