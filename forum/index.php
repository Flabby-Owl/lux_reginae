<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$data = forum_load();
$categoryId = (string) ($_GET['category'] ?? '');
$category = $categoryId !== '' ? forum_find_category($data, $categoryId) : null;

if ($categoryId !== '' && $category === null) {
    http_response_code(404);
    forum_header('Categorie introuvable');
    ?>
    <div class="alert alert-warning">Cette categorie n existe pas.</div>
    <a class="btn btn-primary" href="./">Retour au forum</a>
    <?php
    forum_footer();
    exit;
}

forum_header($category ? $category['name'] : 'Forum Lux Reginae');

if ($category === null):
    ?>
    <div class="forum-toolbar">
      <div>
        <h2 class="mb-1">Categories</h2>
        <p class="text-muted mb-0">Choisissez un espace de discussion.</p>
      </div>
      <a class="btn btn-primary" href="./new.php"><i class="bi bi-plus-lg"></i> Nouveau sujet</a>
    </div>

    <?php foreach ($data['categories'] as $item): ?>
      <?php $topics = forum_topics_for_category($data, $item['id']); ?>
      <a class="forum-category" href="./?category=<?= urlencode($item['id']) ?>">
        <div>
          <h2><?= forum_h($item['name']) ?></h2>
          <p><?= forum_h($item['description']) ?></p>
        </div>
        <div class="forum-count">
          <strong><?= count($topics) ?></strong>
          <span>sujets</span>
        </div>
      </a>
    <?php endforeach; ?>
<?php else: ?>
    <?php $topics = forum_topics_for_category($data, $category['id']); ?>
    <div class="forum-toolbar">
      <div>
        <a href="./" class="small">&larr; Toutes les categories</a>
        <h2 class="mb-1"><?= forum_h($category['name']) ?></h2>
        <p class="text-muted mb-0"><?= forum_h($category['description']) ?></p>
      </div>
      <a class="btn btn-primary" href="./new.php?category=<?= urlencode($category['id']) ?>"><i class="bi bi-plus-lg"></i> Nouveau sujet</a>
    </div>

    <?php if ($topics === []): ?>
      <div class="forum-empty">Aucun sujet pour le moment. C est le bon moment pour lancer la discussion.</div>
    <?php endif; ?>

    <?php foreach ($topics as $topic): ?>
      <?php $replies = forum_replies_for_topic($data, (int) $topic['id']); ?>
      <article class="forum-topic">
        <h2><a href="./topic.php?id=<?= (int) $topic['id'] ?>"><?= forum_h($topic['title']) ?></a></h2>
        <p><?= forum_h(forum_excerpt($topic['body'])) ?></p>
        <p class="forum-post__meta mt-2">
          Par <?= forum_h($topic['author']) ?> -
          <?= date('d/m/Y H:i', strtotime($topic['created_at'])) ?> -
          <?= count($replies) ?> reponse<?= count($replies) > 1 ? 's' : '' ?>
        </p>
      </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php forum_footer(); ?>
