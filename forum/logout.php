<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

forum_logout_user();
forum_redirect('./');
