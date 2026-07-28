<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$data = forum_load();
$selectedCategory = (string) ($_GET['category'] ?? ($_POST['category'] ?? ''));
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = forum_post_value('author');
    $title = forum_post_value('title');
    $body = forum_post_value('body');
    $categoryId = forum_post_value('category');
    $website = forum_post_value('website');

    if ($website !== '') {
        $errors[] = 'Le formulaire semble invalide.';
    }

    if (forum_find_category($data, $categoryId) === null) {
        $errors[] = 'Choisissez une categorie valide.';
    }

    if (forum_length($author) < 2 || forum_length($author) > 40) {
        $errors[] = 'Le pseudo doit faire entre 2 et 40 caracteres.';
    }

    if (forum_length($title) < 4 || forum_length($title) > 120) {
        $errors[] = 'Le titre doit faire entre 4 et 120 caracteres.';
    }

    if (forum_length($body) < 10 || forum_length($body) > 5000) {
        $errors[] = 'Le message doit faire entre 10 et 5000 caracteres.';
    }

    if ($errors === []) {
        [$data, $topicId] = forum_update(static function (array &$forum) use ($author, $title, $body, $categoryId): int {
            $now = date(DATE_ATOM);
            $topicId = (int) $forum['next_topic_id'];
            $forum['next_topic_id'] = $topicId + 1;
            $forum['topics'][] = [
                'id' => $topicId,
                'category_id' => $categoryId,
                'title' => $title,
                'body' => $body,
                'author' => $author,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            return $topicId;
        });

        forum_redirect('./topic.php?id=' . $topicId);
    }
}

forum_header('Nouveau sujet');
forum_error_list($errors);
?>

<div class="forum-toolbar">
  <div>
    <a href="./" class="small">&larr; Retour au forum</a>
    <h2 class="mb-1">Creer un sujet</h2>
    <p class="text-muted mb-0">Lancez une discussion pour la compagnie libre.</p>
  </div>
</div>

<form class="forum-form" method="post" action="./new.php">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="category">Categorie</label>
      <select class="form-select" id="category" name="category" required>
        <option value="">Choisir...</option>
        <?php foreach ($data['categories'] as $category): ?>
          <option value="<?= forum_h($category['id']) ?>" <?= $selectedCategory === $category['id'] ? 'selected' : '' ?>>
            <?= forum_h($category['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="author">Pseudo</label>
      <input class="form-control" id="author" name="author" maxlength="40" required value="<?= forum_h($_POST['author'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label" for="title">Titre</label>
      <input class="form-control" id="title" name="title" maxlength="120" required value="<?= forum_h($_POST['title'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label" for="body">Message</label>
      <textarea class="form-control" id="body" name="body" rows="9" required><?= forum_h($_POST['body'] ?? '') ?></textarea>
    </div>
    <div class="d-none">
      <label for="website">Site web</label>
      <input id="website" name="website" tabindex="-1" autocomplete="off">
    </div>
    <div class="col-12">
      <button class="btn btn-primary" type="submit">Publier le sujet</button>
    </div>
  </div>
</form>

<?php forum_footer(); ?>
