<?php
require_once __DIR__ . '/includes/layout.php';

$data = forum_load();
$categoryId = (string) ($_GET['category'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && lr_has_role(['Administrateur']) && lr_validate_csrf()) {
    $name = trim(lr_post('name'));
    if ($name !== '') {
        forum_update(function (&$forum) use ($name): void {
            $forum['categories'][] = [
                'id' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . time(),
                'name' => $name,
                'description' => trim(lr_post('description')),
            ];
        });
    }
    header('Location: ./');
    exit;
}

forum_header($categoryId === '' ? 'Forum Lux Reginae' : (forum_category($data, $categoryId)['name'] ?? 'Categorie'));

if ($categoryId === ''):
    $topicTotal = count($data['topics']);
    $replyTotal = count($data['replies']);
?>
<section class="forum-overview">
  <div>
    <p class="forum-kicker">Accueil du forum</p>
    <h2>Choisis un espace de discussion</h2>
    <p>Les sujets sont classés par catégorie pour retrouver vite les annonces, les sorties et les demandes d’aide.</p>
  </div>
  <div class="forum-stats">
    <span><strong><?= (int) $topicTotal ?></strong> sujets</span>
    <span><strong><?= (int) $replyTotal ?></strong> réponses</span>
  </div>
</section>

<div class="forum-toolbar">
  <h2>Categories</h2>
  <?php if (lr_has_role(['Administrateur', 'Modérateur', 'Utilisateur'])): ?>
    <a class="btn btn-primary" href="./new.php">Nouveau sujet</a>
  <?php else: ?>
    <a class="btn btn-outline-primary" href="../login.php">Se connecter pour publier</a>
  <?php endif; ?>
</div>

<div class="forum-category-grid">
<?php foreach ($data['categories'] as $category):
    $topics = forum_topics($data, $category['id']);
    $replyCount = array_sum(array_map(fn ($topic) => forum_reply_count($data, (int) $topic['id']), $topics));
?>
  <a class="forum-category" href="./?category=<?= urlencode($category['id']) ?>">
    <span class="forum-category__icon"><?= strtoupper(substr($category['name'], 0, 1)) ?></span>
    <div>
      <h2><?= lr_h($category['name']) ?></h2>
      <p><?= lr_h($category['description']) ?></p>
      <small><?= count($topics) ?> sujets · <?= (int) $replyCount ?> réponses</small>
    </div>
  </a>
<?php endforeach; ?>
</div>

<?php if (lr_has_role(['Administrateur'])): ?>
<form class="forum-form forum-form--compact mt-4" method="post">
  <?php lr_csrf_field(); ?>
  <h2>Créer une catégorie</h2>
  <div class="forum-form-grid">
    <label>Nom<input class="form-control" name="name" placeholder="Ex : Raids, artisanat, annonces"></label>
    <label>Description<input class="form-control" name="description" placeholder="Courte description visible sur la carte"></label>
  </div>
  <button class="btn btn-primary">Ajouter</button>
</form>
<?php endif; ?>

<?php else:
    $category = forum_category($data, $categoryId);
    $topics = forum_topics($data, $categoryId);
?>
<div class="forum-toolbar">
  <div>
    <a class="forum-back" href="./">Retour aux catégories</a>
    <h2><?= lr_h($category['name'] ?? 'Categorie') ?></h2>
    <?php if ($category): ?><p><?= lr_h($category['description']) ?></p><?php endif; ?>
  </div>
  <a class="btn btn-primary" href="./new.php?category=<?= urlencode($categoryId) ?>">Nouveau sujet</a>
</div>

<div class="forum-topic-list">
<?php foreach ($topics as $topic): ?>
  <article class="forum-topic">
    <div>
      <h2><a href="./topic.php?id=<?= (int) $topic['id'] ?>"><?= lr_h($topic['title']) ?></a></h2>
      <?php $excerpt = strlen((string) $topic['body']) > 150 ? substr((string) $topic['body'], 0, 150) . '...' : (string) $topic['body']; ?>
      <p><?= lr_h($excerpt) ?></p>
      <small>Par <?= lr_h($topic['author']) ?> · cree le <?= date('d/m/Y H:i', strtotime($topic['created_at'])) ?></small>
    </div>
    <div class="forum-topic__meta">
      <strong><?= forum_reply_count($data, (int) $topic['id']) ?></strong>
      <span>réponses</span>
      <small><?= lr_h(forum_latest_activity($data, $topic)) ?></small>
    </div>
  </article>
<?php endforeach; ?>
</div>

<?php if (!$topics): ?>
  <div class="forum-empty">Aucun sujet dans cette catégorie pour le moment.</div>
<?php endif; ?>
<?php endif; forum_footer(); ?>
