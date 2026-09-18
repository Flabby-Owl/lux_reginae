<?php
require_once __DIR__ . '/site_backend/auth.php';
lr_logout();
header('Location: ./index.php');
