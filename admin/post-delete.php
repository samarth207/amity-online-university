<?php

declare(strict_types=1);

require_once __DIR__ . '/../cms/functions.php';

ensure_cms_tables();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM blog_posts WHERE id = :id');
    $stmt->execute(['id' => $id]);
    regenerate_sitemap();
}

header('Location: /admin/index.php');
exit;
