<?php

declare(strict_types=1);

require_once __DIR__ . '/cms/functions.php';

ensure_cms_tables();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Invalid request']);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$course = trim((string) ($_POST['course'] ?? ''));
$blogPostId = (int) ($_POST['blog_post_id'] ?? 0);
$sourceUrl = substr(current_full_url(), 0, 255);

if ($name === '' || $course === '' || !preg_match('/^[0-9]{10}$/', $phone)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Invalid form data']);
    exit;
}

$stmt = db()->prepare('INSERT INTO blog_leads (blog_post_id, name, phone, course, source_url) VALUES (:blog_post_id, :name, :phone, :course, :source_url)');
$stmt->execute([
    'blog_post_id' => $blogPostId > 0 ? $blogPostId : null,
    'name' => $name,
    'phone' => $phone,
    'course' => $course,
    'source_url' => $sourceUrl,
]);

echo json_encode(['ok' => true]);
