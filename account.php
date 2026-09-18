<?php
require_once __DIR__ . '/site_backend/auth.php';
lr_require_login();
$user = lr_current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_self' && lr_validate_csrf()) {
    lr_update_users(function (array &$users) use ($user): void { $users = array_values(array_filter($users, fn ($item) => $item['id'] !== $user['id'])); });
    lr_logout();
    header('Location: ./index.php');
    exit;
}
if (($_GET['export'] ?? '') === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="lux-reginae-compte.json"');
    echo json_encode(['id'=>$user['id'],'username'=>$user['username'],'role'=>$user['role'],'created_at'=>$user['created_at'],'last_login_at'=>$user['last_login_at'],'privacy_accepted_at'=>$user['privacy_accepted_at']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
$title = 'Mon compte';
include __DIR__ . '/site_backend/page_header.php';
?>
<section class="auth-card">
  <h1><?= lr_h($user['username']) ?></h1>
  <p><strong>Rôle :</strong> <?= lr_h($user['role']) ?></p>
  <p><strong>Compte créé :</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
  <a class="btn btn-primary" href="./account.php?export=json">Exporter mes données</a>
  <?php if ($user['role'] === 'Administrateur'): ?><a class="btn btn-outline-primary" href="./admin.php">Gestion globale</a><?php endif; ?>
</section>
<form class="auth-card mt-3" method="post">
  <?php lr_csrf_field(); ?>
  <input type="hidden" name="action" value="delete_self">
  <h2>Supprimer mon compte</h2>
  <p class="text-muted">Le compte est supprimé. Les anciens contenus publics peuvent rester affichés pour conserver la cohérence des discussions.</p>
  <button class="btn btn-outline-danger">Supprimer mon compte</button>
</form>
<?php include __DIR__ . '/site_backend/page_footer.php'; ?>
