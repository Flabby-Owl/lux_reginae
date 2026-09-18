<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

lr_require_role(['Administrateur', 'Modérateur', 'Utilisateur']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    lr_json_response(['error' => 'Aucune image envoyée'], 422);
}

$file = $_FILES['image'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    lr_json_response(['error' => 'Upload impossible'], 422);
}

if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
    lr_json_response(['error' => 'Image trop lourde : 5 Mo maximum'], 422);
}

$allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
$mime = mime_content_type($file['tmp_name']);
if (!isset($allowed[$mime])) {
    lr_json_response(['error' => 'Format non autorisé'], 422);
}

if (@getimagesize($file['tmp_name']) === false) {
    lr_json_response(['error' => 'Le fichier envoyé n’est pas une image valide'], 422);
}

$targetDir = __DIR__ . '/../site_img/uploads';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0775, true);
}

$name = 'upload-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
$target = $targetDir . '/' . $name;
if (!move_uploaded_file($file['tmp_name'], $target)) {
    lr_json_response(['error' => 'Impossible de stocker l’image sur le site'], 500);
}

lr_json_response(['path' => './site_img/uploads/' . $name]);
