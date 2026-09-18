<?php
require_once __DIR__ . '/site_backend/auth.php';
$auth = lr_public_auth_context();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Lux Reginae, compagnie libre francophone sur Final Fantasy XIV, Chaos - Moogle.">
  <title>Lux Reginae - Compagnie Libre FFXIV</title>
  <script>window.LR_AUTH = <?= json_encode($auth, JSON_UNESCAPED_UNICODE) ?>;</script>
  <link rel="icon" type="image/png" href="./site_img/logo_lxr.png">
  <link rel="apple-touch-icon" href="./site_img/logo_lxr.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="./site_css/theme.css">
  <link rel="stylesheet" href="./site_css/admin.css">
  <link rel="stylesheet" href="./forum/forum.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top site-nav">
    <div class="container">
      <a class="navbar-brand" href="#home" data-route="home"><img class="brand-mark" src="./site_img/logo_lxr.png" alt=""> Lux Reginae</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Afficher la navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto" id="main-navigation"></ul>
        <div class="d-flex flex-wrap gap-2 ms-lg-3 mt-3 mt-lg-0">
          <?php if ($auth['user']): ?>
            <a class="btn btn-outline-light btn-sm" href="./account.php"><?= lr_h($auth['user']['username']) ?> · <?= lr_h($auth['user']['role']) ?></a>
            <a class="btn btn-outline-light btn-sm" href="./logout.php">Déconnexion</a>
          <?php else: ?>
            <a class="btn btn-outline-light btn-sm" href="./login.php">Connexion</a>
            <a class="btn btn-primary btn-sm" href="./register.php">Inscription</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  <header class="site-hero">
    <img src="./site_img/banner.png" alt="Bannière Lux Reginae" class="site-hero__image">
    <div class="site-hero__overlay">
      <div class="container">
        <p class="eyebrow">Compagnie Libre francophone</p>
        <img class="hero-logo" src="./site_img/logo_lxr.png" alt="Logo Lux Reginae">
        <h1>Lux Reginae</h1>
        <p class="hero-copy">Une compagnie chill sur Final Fantasy XIV, active sur Chaos - Moogle.</p>
      </div>
    </div>
  </header>
  <main id="app" class="site-main" tabindex="-1"></main>
  <?php if ($auth['permissions']['editSite'] || $auth['permissions']['editWiki']): ?>
    <button class="edit-fab" type="button" id="edit-toggle"><i class="bi bi-pencil-square"></i> Édition</button>
  <?php endif; ?>
  <footer class="site-footer">
    <div class="container">
      <span class="footer-brand"><img src="./site_img/logo_lxr.png" alt=""> Lux Reginae - Chaos / Moogle</span>
      <div class="d-flex flex-wrap gap-3">
        <a href="./about.php">À propos</a>
        <a href="./privacy.php">Confidentialité</a>
        <a href="https://eu.finalfantasyxiv.com/lodestone/freecompany/9233364398528184114/" target="_blank" rel="noreferrer">Profil Lodestone</a>
      </div>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./site_js/app.js"></script>
</body>
</html>

