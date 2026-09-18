<?php
require_once __DIR__ . '/includes/layout.php';

$data = forum_load();
$id = (int) ($_GET['id'] ?? 0);
$topic = forum_topic($data, $id);

if (!$topic) {
    forum_header('Sujet introuvable');
    echo '<div class="forum-empty">Sujet introuvable.</div>';
    forum_footer();
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    lr_require_login();
    if (!lr_validate_csrf()) $errors[] = 'Session expiree.';

    if (($_POST['action'] ?? '') === 'delete' && lr_has_role(['Administrateur', 'Modérateur']) && !$errors) {
        forum_update(function (array &$forum) use ($id): void {
            $forum['topics'] = array_values(array_filter($forum['topics'], fn ($t) => (int) $t['id'] !== $id));
            $forum['replies'] = array_values(array_filter($forum['replies'], fn ($r) => (int) $r['topic_id'] !== $id));
        });
        header('Location: ./');
        exit;
    }

    if (($_POST['action'] ?? '') === 'delete_reply' && lr_has_role(['Administrateur', 'Modérateur']) && !$errors) {
        $replyId = (int) lr_post('reply_id');
        forum_update(function (array &$forum) use ($replyId): void {
            $forum['replies'] = array_values(array_filter($forum['replies'], fn ($r) => (int) $r['id'] !== $replyId));
        });
        header('Location: ./topic.php?id=' . $id);
        exit;
    }

    if (($_POST['action'] ?? '') === 'reply' && !$errors) {
        $body = trim(lr_post('body'));
        if ($body === '') {
            $errors[] = 'La réponse est vide.';
        } else {
            $user = lr_current_user();
            forum_update(function (&$forum) use ($user, $id, $body): void {
                $forum['replies'][] = [
                    'id' => (int) $forum['next_reply_id']++,
                    'topic_id' => $id,
                    'body' => $body,
                    'author' => $user['username'],
                    'author_id' => $user['id'],
                    'created_at' => date(DATE_ATOM),
                ];
                foreach ($forum['topics'] as &$topicItem) {
                    if ((int) $topicItem['id'] === $id) {
                        $topicItem['updated_at'] = date(DATE_ATOM);
                    }
                }
                unset($topicItem);
            });
            header('Location: ./topic.php?id=' . $id);
            exit;
        }
    }
}

$data = forum_load();
$topic = forum_topic($data, $id);
$category = forum_category($data, (string) $topic['category_id']);
$replies = forum_replies($data, $id);

forum_header($topic['title']);
forum_errors($errors);
?>
<div class="forum-toolbar">
  <div>
    <a class="forum-back" href="./?category=<?= urlencode($topic['category_id']) ?>">Retour à <?= lr_h($category['name'] ?? 'la catégorie') ?></a>
    <h2><?= lr_h($topic['title']) ?></h2>
    <p><?= count($replies) ?> réponses · dernière activité <?= lr_h(forum_latest_activity($data, $topic)) ?></p>
  </div>
  <?php if (lr_has_role(['Administrateur', 'Modérateur'])): ?>
    <form method="post">
      <?php lr_csrf_field(); ?>
      <button class="btn btn-outline-danger" name="action" value="delete">Supprimer le sujet</button>
    </form>
  <?php endif; ?>
</div>

<article class="forum-post forum-post--lead">
  <aside class="forum-author">
    <span><?= strtoupper(substr($topic['author'], 0, 1)) ?></span>
    <strong><?= lr_h($topic['author']) ?></strong>
    <small><?= date('d/m/Y H:i', strtotime($topic['created_at'])) ?></small>
  </aside>
  <div class="forum-post__body"><?= nl2br(lr_h($topic['body'])) ?></div>
</article>

<section class="forum-replies">
  <h2>Réponses</h2>
  <?php foreach ($replies as $reply): ?>
    <article class="forum-post">
      <aside class="forum-author">
        <span><?= strtoupper(substr($reply['author'], 0, 1)) ?></span>
        <strong><?= lr_h($reply['author']) ?></strong>
        <small><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></small>
      </aside>
      <div>
        <div class="forum-post__body"><?= nl2br(lr_h($reply['body'])) ?></div>
        <?php if (lr_has_role(['Administrateur', 'Modérateur'])): ?>
          <form method="post" class="forum-inline-action">
            <?php lr_csrf_field(); ?>
            <input type="hidden" name="action" value="delete_reply">
            <input type="hidden" name="reply_id" value="<?= (int) $reply['id'] ?>">
            <button class="btn btn-outline-danger btn-sm">Supprimer la réponse</button>
          </form>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$replies): ?><div class="forum-empty">Aucune réponse pour le moment.</div><?php endif; ?>
</section>

<?php if (lr_current_user()): ?>
<form class="forum-form mt-4" method="post">
  <?php lr_csrf_field(); ?>
  <input type="hidden" name="action" value="reply">
  <h2>Répondre</h2>
  <label>Message<textarea class="form-control" name="body" rows="7" placeholder="Écris ta réponse..."></textarea></label>
  <div class="forum-form-actions"><button class="btn btn-primary">Publier la réponse</button></div>
</form>
<?php else: ?>
  <div class="forum-empty"><a href="../login.php">Connectez-vous</a> pour repondre.</div>
<?php endif; ?>
<?php forum_footer(); ?>
