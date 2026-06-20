<?php

declare(strict_types=1);

require_once __DIR__ . '/../cms/functions.php';

ensure_cms_tables();
require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    http_response_code(422);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$mime = mime_content_type($file['tmp_name']) ?: '';
$allowed = ['image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/png' => 'png'];

if (!isset($allowed[$mime])) {
    http_response_code(422);
    echo json_encode(['error' => 'Only JPG, PNG or WebP allowed']);
    exit;
}

if (!is_dir(BLOG_UPLOAD_DIR)) {
    mkdir(BLOG_UPLOAD_DIR, 0775, true);
}

$filename = 'content-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
$targetPath = BLOG_UPLOAD_DIR . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

echo json_encode(['location' => BLOG_UPLOAD_URL . '/' . $filename]);
