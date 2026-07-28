<?php

declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function forum_header(string $title): void
{
    ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= forum_h($title) ?> - Forum Lux Reginae</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../site_css/theme.css">
  <link rel="stylesheet" href="./forum.css">
</head>
<body class="forum-body">
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top site-nav">
    <div class="container">
      <a class="navbar-brand" href="../index.html">
        <span class="brand-mark">LR</span>
        Lux Reginae
      </a>
      <div class="ms-auto d-flex gap-2">
        <a class="btn btn-outline-light btn-sm" href="../index.html#space">Site</a>
        <a class="btn btn-primary btn-sm" href="./">Forum</a>
      </div>
    </div>
  </nav>
  <header class="forum-hero">
    <div class="container">
      <p class="eyebrow">Forum communautaire</p>
      <h1><?= forum_h($title) ?></h1>
      <p>Discussions, sorties, entraide et annonces pour la compagnie libre.</p>
    </div>
  </header>
  <main class="forum-main">
    <div class="container">
    <?php
}

function forum_footer(): void
{
    ?>
    </div>
  </main>
  <footer class="site-footer">
    <div class="container">
      <span>Forum Lux Reginae</span>
      <a href="../index.html">Retour au site</a>
    </div>
  </footer>
</body>
</html>
    <?php
}

function forum_error_list(array $errors): void
{
    if ($errors === []) {
        return;
    }

    ?>
    <div class="alert alert-danger">
      <strong>Impossible de valider le formulaire.</strong>
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= forum_h($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
}
