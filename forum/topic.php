<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$topicId = (int) ($_GET['id'] ?? 0);
$data = forum_load();
$topic = forum_find_topic($data, $topicId);

if ($topic === null) {
    http_response_code(404);
    forum_header('Sujet introuvable');
    ?>
    <div class="alert alert-warning">Ce sujet n existe pas.</div>
    <a class="btn btn-primary" href="./">Retour au forum</a>
    <?php
    forum_footer();
    exit;
}

$category = forum_find_category($data, $topic['category_id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = forum_post_value('author');
    $body = forum_post_value('body');
    $website = forum_post_value('website');

    if ($website !== '') {
        $errors[] = 'Le formulaire semble invalide.';
    }

    if (forum_length($author) < 2 || forum_length($author) > 40) {
        $errors[] = 'Le pseudo doit faire entre 2 et 40 caracteres.';
    }

    if (forum_length($body) < 3 || forum_length($body) > 5000) {
        $errors[] = 'La reponse doit faire entre 3 et 5000 caracteres.';
    }

    if ($errors === []) {
        forum_update(static function (array &$forum) use ($author, $body, $topicId): int {
            $now = date(DATE_ATOM);
            $replyId = (int) $forum['next_reply_id'];
            $forum['next_reply_id'] = $replyId + 1;
            $forum['replies'][] = [
                'id' => $replyId,
                'topic_id' => $topicId,
                'body' => $body,
                'author' => $author,
                'created_at' => $now,
            ];

            foreach ($forum['topics'] as &$item) {
                if ((int) $item['id'] === $topicId) {
                    $item['updated_at'] = $now;
                    break;
                }
            }

            return $replyId;
        });

        forum_redirect('./topic.php?id=' . $topicId . '#reply-form');
    }
}

$data = forum_load();
$topic = forum_find_topic($data, $topicId);
$replies = forum_replies_for_topic($data, $topicId);

forum_header($topic['title']);
forum_error_list($errors);
?>

<div class="forum-toolbar">
  <div>
    <a href="./<?= $category ? '?category=' . urlencode($category['id']) : '' ?>" class="small">&larr; Retour <?= $category ? 'a ' . forum_h($category['name']) : 'au forum' ?></a>
    <h2 class="mb-1"><?= forum_h($topic['title']) ?></h2>
    <p class="text-muted mb-0">Sujet cree par <?= forum_h($topic['author']) ?> le <?= date('d/m/Y H:i', strtotime($topic['created_at'])) ?></p>
  </div>
  <a class="btn btn-primary" href="#reply-form"><i class="bi bi-reply-fill"></i> Repondre</a>
</div>

<article class="forum-post">
  <p class="forum-post__meta">Message initial - <?= forum_h($topic['author']) ?></p>
  <div class="forum-post__body"><?= nl2br(forum_h($topic['body'])) ?></div>
</article>

<?php foreach ($replies as $reply): ?>
  <article class="forum-post">
    <p class="forum-post__meta">
      Reponse de <?= forum_h($reply['author']) ?> -
      <?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?>
    </p>
    <div class="forum-post__body"><?= nl2br(forum_h($reply['body'])) ?></div>
  </article>
<?php endforeach; ?>

<form class="forum-form mt-4" id="reply-form" method="post" action="./topic.php?id=<?= (int) $topicId ?>#reply-form">
  <h2>Repondre</h2>
  <div class="row g-3">
    <div class="col-md-5">
      <label class="form-label" for="author">Pseudo</label>
      <input class="form-control" id="author" name="author" maxlength="40" required value="<?= forum_h($_POST['author'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label" for="body">Message</label>
      <textarea class="form-control" id="body" name="body" rows="7" required><?= forum_h($_POST['body'] ?? '') ?></textarea>
    </div>
    <div class="d-none">
      <label for="website">Site web</label>
      <input id="website" name="website" tabindex="-1" autocomplete="off">
    </div>
    <div class="col-12">
      <button class="btn btn-primary" type="submit">Publier la reponse</button>
    </div>
  </div>
</form>

<?php forum_footer(); ?>
