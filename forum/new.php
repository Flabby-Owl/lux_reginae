<?php
require_once __DIR__ . '/includes/layout.php';

lr_require_login();
$user = lr_current_user();
$data = forum_load();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lr_validate_csrf()) $errors[] = 'Session expiree.';
    if (!forum_category($data, lr_post('category'))) $errors[] = 'Categorie invalide.';
    if (strlen(trim(lr_post('title'))) < 4) $errors[] = 'Titre trop court.';
    if (strlen(trim(lr_post('body'))) < 3) $errors[] = 'Message trop court.';

    if (!$errors) {
        $id = forum_update(function (&$forum) use ($user): int {
            $id = (int) $forum['next_topic_id']++;
            $now = date(DATE_ATOM);
            $forum['topics'][] = [
                'id' => $id,
                'category_id' => lr_post('category'),
                'title' => trim(lr_post('title')),
                'body' => trim(lr_post('body')),
                'author' => $user['username'],
                'author_id' => $user['id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
            return $id;
        });
        header('Location: ./topic.php?id=' . $id);
        exit;
    }
}

forum_header('Nouveau sujet');
forum_errors($errors);
?>
<div class="forum-toolbar">
  <div>
    <a class="forum-back" href="./">Retour au forum</a>
    <h2>Publier un sujet</h2>
    <p>Choisis la bonne catégorie, donne un titre clair, puis détaille ta demande ou ton annonce.</p>
  </div>
</div>

<form class="forum-form" method="post">
  <?php lr_csrf_field(); ?>
  <div class="forum-form-grid">
    <label>Categorie
      <select class="form-select" name="category">
        <?php foreach ($data['categories'] as $cat): ?>
          <option value="<?= lr_h($cat['id']) ?>" <?= ($_GET['category'] ?? '') === $cat['id'] ? 'selected' : '' ?>><?= lr_h($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Titre
      <input class="form-control" name="title" value="<?= lr_h(lr_post('title')) ?>" placeholder="Ex : Sortie cartes vendredi soir">
    </label>
  </div>
  <label>Message
    <textarea class="form-control" name="body" rows="9" placeholder="Ajoute les infos importantes : date, horaire, prerequis, liens utiles..."><?= lr_h(lr_post('body')) ?></textarea>
  </label>
  <div class="forum-form-actions">
    <button class="btn btn-primary">Publier</button>
    <a class="btn btn-outline-primary" href="./">Annuler</a>
  </div>
</form>
<?php forum_footer(); ?>
