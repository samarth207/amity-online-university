<?php

declare(strict_types=1);

require_once __DIR__ . '/../cms/functions.php';

ensure_cms_tables();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request');
}

function upload_image_if_present(string $fieldName, bool $required = false): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            throw new RuntimeException('Required image missing: ' . $fieldName);
        }
        return null;
    }

    if (!is_uploaded_file($_FILES[$fieldName]['tmp_name'])) {
        throw new RuntimeException('Invalid upload for ' . $fieldName);
    }

    $mime = mime_content_type($_FILES[$fieldName]['tmp_name']) ?: '';
    $allowed = ['image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/png' => 'png'];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG or WebP images are allowed');
    }

    if (!is_dir(BLOG_UPLOAD_DIR)) {
        mkdir(BLOG_UPLOAD_DIR, 0775, true);
    }

    $filename = $fieldName . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $targetPath = BLOG_UPLOAD_DIR . '/' . $filename;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        throw new RuntimeException('Unable to save image: ' . $fieldName);
    }

    return BLOG_UPLOAD_URL . '/' . $filename;
}

function unique_slug(string $slug, int $postId = 0): string
{
    $slug = slugify($slug);
    $candidate = $slug;
    $i = 2;

    while (true) {
        $sql = 'SELECT id FROM blog_posts WHERE slug = :slug' . ($postId > 0 ? ' AND id != :id' : '') . ' LIMIT 1';
        $stmt = db()->prepare($sql);
        $params = ['slug' => $candidate];
        if ($postId > 0) {
            $params['id'] = $postId;
        }
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $candidate;
        }
        $candidate = $slug . '-' . $i;
        $i++;
    }
}

$id = (int) ($_POST['id'] ?? 0);
$title = trim((string) ($_POST['title'] ?? ''));
$metaTitle = trim((string) ($_POST['meta_title'] ?? ''));
$metaDescription = trim((string) ($_POST['meta_description'] ?? ''));
$focusKeyword = trim((string) ($_POST['focus_keyword'] ?? ''));
$primaryKeyword = trim((string) ($_POST['primary_keyword'] ?? ''));
$slugInput = trim((string) ($_POST['slug'] ?? $title));
$excerpt = trim((string) ($_POST['excerpt'] ?? ''));
$contentHtml = trim((string) ($_POST['content_html'] ?? ''));
$featureImageAlt = trim((string) ($_POST['feature_image_alt'] ?? ''));
$featureImageTitle = trim((string) ($_POST['feature_image_title'] ?? ''));
$authorName = trim((string) ($_POST['author_name'] ?? ''));
$authorBio = trim((string) ($_POST['author_bio'] ?? ''));
$authorPage = trim((string) ($_POST['author_page'] ?? ''));
$tags = normalize_tags((string) ($_POST['tags'] ?? ''));
$status = trim((string) ($_POST['status'] ?? 'draft'));
$publishDate = trim((string) ($_POST['publish_date'] ?? ''));
$publishTime = trim((string) ($_POST['publish_time'] ?? ''));
$ctaHeading = trim((string) ($_POST['cta_heading'] ?? ''));
$ctaButtonText = trim((string) ($_POST['cta_button_text'] ?? ''));

$faqQuestions = $_POST['faq_question'] ?? [];
$faqAnswers = $_POST['faq_answer'] ?? [];
$faq = [];

for ($i = 0; $i < count($faqQuestions); $i++) {
    $q = trim((string) ($faqQuestions[$i] ?? ''));
    $a = trim((string) ($faqAnswers[$i] ?? ''));
    if ($q !== '' && $a !== '') {
        $faq[] = ['q' => $q, 'a' => $a];
    }
}

$categories = $_POST['categories'] ?? [];
if (!is_array($categories)) {
    $categories = [];
}
$categories = array_values(array_unique(array_filter(array_map('trim', $categories))));

$allowedStatuses = ['draft', 'pending', 'published', 'scheduled'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'draft';
}

$errors = [];
if ($title === '' || strlen($title) > 70) {
    $errors[] = 'Blog title is required and must be up to 70 characters.';
}
if ($metaTitle === '' || strlen($metaTitle) > 60) {
    $errors[] = 'Meta title is required and must be up to 60 characters.';
}
if ($metaDescription === '' || strlen($metaDescription) > 250 || strlen($metaDescription) < 200) {
    $errors[] = 'Meta description must be between 200 and 250 characters.';
}
if ($focusKeyword === '') {
    $errors[] = 'Focus keyword is required.';
}
if ($excerpt === '' || strlen($excerpt) > 250) {
    $errors[] = 'Excerpt is required and must be up to 250 characters.';
}
if ($contentHtml === '') {
    $errors[] = 'Blog content is required.';
}
if ($featureImageAlt === '') {
    $errors[] = 'Feature image alt text is required.';
}
if ($authorName === '') {
    $errors[] = 'Author name is required.';
}
if (count($categories) === 0) {
    $errors[] = 'At least one category is required.';
}
if ($status === 'scheduled' && $publishDate === '') {
    $errors[] = 'Publish date is required for scheduled posts.';
}

$existingPost = null;
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $existingPost = $stmt->fetch();
}

try {
    $featureImage = upload_image_if_present('feature_image', $existingPost ? false : true);
    $authorImage = upload_image_if_present('author_image', false);
} catch (RuntimeException $e) {
    $errors[] = $e->getMessage();
}

if ($existingPost && !$featureImage) {
    $featureImage = (string) $existingPost['feature_image'];
}
if ($existingPost && !$authorImage) {
    $authorImage = (string) ($existingPost['author_image'] ?? '');
}

if (!$featureImage) {
    $errors[] = 'Feature image is required.';
}

if ($errors) {
    http_response_code(422);
    echo '<h3>Validation errors</h3><ul>';
    foreach ($errors as $error) {
        echo '<li>' . h($error) . '</li>';
    }
    echo '</ul><p><a href="javascript:history.back()">Go Back</a></p>';
    exit;
}

$slug = unique_slug($slugInput !== '' ? $slugInput : $title, $id);
$publishAt = build_publish_at($publishDate, $publishTime);
$contentHtml = sanitize_rich_html($contentHtml);
$faqJson = json_encode($faq, JSON_UNESCAPED_UNICODE);
$categoriesJson = json_encode($categories, JSON_UNESCAPED_UNICODE);

if ($id > 0) {
    $sql = 'UPDATE blog_posts SET
        meta_title = :meta_title,
        meta_description = :meta_description,
        focus_keyword = :focus_keyword,
        primary_keyword = :primary_keyword,
        title = :title,
        slug = :slug,
        excerpt = :excerpt,
        content_html = :content_html,
        faq_json = :faq_json,
        cta_heading = :cta_heading,
        cta_button_text = :cta_button_text,
        feature_image = :feature_image,
        feature_image_alt = :feature_image_alt,
        feature_image_title = :feature_image_title,
        author_name = :author_name,
        author_bio = :author_bio,
        author_image = :author_image,
        author_page = :author_page,
        categories_json = :categories_json,
        tags = :tags,
        status = :status,
        publish_at = :publish_at
    WHERE id = :id';

    $stmt = db()->prepare($sql);
    $stmt->execute([
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
        'focus_keyword' => $focusKeyword,
        'primary_keyword' => $primaryKeyword ?: null,
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $excerpt,
        'content_html' => $contentHtml,
        'faq_json' => $faqJson,
        'cta_heading' => $ctaHeading ?: null,
        'cta_button_text' => $ctaButtonText ?: null,
        'feature_image' => $featureImage,
        'feature_image_alt' => $featureImageAlt,
        'feature_image_title' => $featureImageTitle ?: null,
        'author_name' => $authorName,
        'author_bio' => $authorBio ?: null,
        'author_image' => $authorImage ?: null,
        'author_page' => $authorPage ?: null,
        'categories_json' => $categoriesJson,
        'tags' => $tags ?: null,
        'status' => $status,
        'publish_at' => $publishAt,
        'id' => $id,
    ]);
} else {
    $sql = 'INSERT INTO blog_posts (
        meta_title, meta_description, focus_keyword, primary_keyword, title, slug, excerpt,
        content_html, faq_json, cta_heading, cta_button_text, feature_image, feature_image_alt,
        feature_image_title, author_name, author_bio, author_image, author_page,
        categories_json, tags, status, publish_at
    ) VALUES (
        :meta_title, :meta_description, :focus_keyword, :primary_keyword, :title, :slug, :excerpt,
        :content_html, :faq_json, :cta_heading, :cta_button_text, :feature_image, :feature_image_alt,
        :feature_image_title, :author_name, :author_bio, :author_image, :author_page,
        :categories_json, :tags, :status, :publish_at
    )';

    $stmt = db()->prepare($sql);
    $stmt->execute([
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
        'focus_keyword' => $focusKeyword,
        'primary_keyword' => $primaryKeyword ?: null,
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $excerpt,
        'content_html' => $contentHtml,
        'faq_json' => $faqJson,
        'cta_heading' => $ctaHeading ?: null,
        'cta_button_text' => $ctaButtonText ?: null,
        'feature_image' => $featureImage,
        'feature_image_alt' => $featureImageAlt,
        'feature_image_title' => $featureImageTitle ?: null,
        'author_name' => $authorName,
        'author_bio' => $authorBio ?: null,
        'author_image' => $authorImage ?: null,
        'author_page' => $authorPage ?: null,
        'categories_json' => $categoriesJson,
        'tags' => $tags ?: null,
        'status' => $status,
        'publish_at' => $publishAt,
    ]);
}

regenerate_sitemap();

header('Location: /admin/index.php');
exit;
