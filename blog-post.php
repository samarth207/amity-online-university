<?php

declare(strict_types=1);

require_once __DIR__ . '/cms/functions.php';

ensure_cms_tables();
regenerate_sitemap_if_stale(1800);

$slug = isset($_GET['slug']) ? slugify((string) $_GET['slug']) : '';
$post = $slug !== '' ? fetch_blog_post_by_slug($slug) : null;

if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.html';
    exit;
}

$isPublic = $post['status'] === 'published' || ($post['status'] === 'scheduled' && !empty($post['publish_at']) && strtotime((string) $post['publish_at']) <= time());
if (!$isPublic) {
    http_response_code(404);
    require __DIR__ . '/404.html';
    exit;
}

$parsed = parse_content_headings((string) $post['content_html']);
$contentHtml = is_array($parsed) && isset($parsed['html']) ? (string) $parsed['html'] : (string) $post['content_html'];
$headings = is_array($parsed) && isset($parsed['headings']) ? $parsed['headings'] : [];

$faq = [];
if (!empty($post['faq_json'])) {
    $faq = json_decode((string) $post['faq_json'], true) ?: [];
}

$categories = json_decode((string) $post['categories_json'], true) ?: [];
$primaryCategory = $categories[0] ?? 'Education';
$publishDate = !empty($post['publish_at']) ? date('F j, Y', strtotime((string) $post['publish_at'])) : date('F j, Y', strtotime((string) $post['created_at']));
$postUrl = SITE_URL . '/blog/' . $post['slug'];
$ctaHeading = trim((string) ($post['cta_heading'] ?? 'Start Your Journey with Amity Online'));
$ctaButton = trim((string) ($post['cta_button_text'] ?? 'Apply Now'));

$faqSchema = [];
if ($faq) {
    foreach ($faq as $item) {
        if (!empty($item['q']) && !empty($item['a'])) {
            $faqSchema[] = [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'],
                ],
            ];
        }
    }
}

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post['meta_title'],
    'description' => $post['meta_description'],
    'image' => SITE_URL . $post['feature_image'],
    'datePublished' => date('c', strtotime((string) ($post['publish_at'] ?: $post['created_at']))),
    'dateModified' => date('c', strtotime((string) $post['updated_at'])),
    'author' => [
        '@type' => 'Person',
        'name' => $post['author_name'],
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => SITE_NAME,
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $postUrl,
    ],
    'keywords' => trim(($post['focus_keyword'] ?? '') . ',' . ($post['tags'] ?? ''), ','),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h((string) $post['meta_title']) ?></title>
    <meta name="description" content="<?= h((string) $post['meta_description']) ?>">
    <meta name="keywords" content="<?= h(trim((string) $post['focus_keyword'] . ',' . (string) ($post['tags'] ?? ''))) ?>">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?= h($postUrl) ?>">

    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= h((string) $post['meta_title']) ?>">
    <meta property="og:description" content="<?= h((string) $post['meta_description']) ?>">
    <meta property="og:url" content="<?= h($postUrl) ?>">
    <meta property="og:image" content="<?= h(SITE_URL . (string) $post['feature_image']) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= h((string) $post['meta_title']) ?>">
    <meta name="twitter:description" content="<?= h((string) $post['meta_description']) ?>">
    <meta name="twitter:image" content="<?= h(SITE_URL . (string) $post['feature_image']) ?>">

    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php if ($faqSchema): ?>
        <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqSchema], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #1e293b; }
        .container { max-width: 1180px; margin: 0 auto; padding: 0 16px; }
        .top { padding: 18px 0; font-size: 0.9rem; }
        .top a { color: #0b3b6e; text-decoration: none; font-weight: 700; }
        .layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 20px; padding-bottom: 50px; }
        .article { background: #fff; border-radius: 14px; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08); overflow: hidden; }
        .hero-img { width: 100%; height: 390px; object-fit: cover; }
        .inner { padding: 22px; }
        .meta { font-size: 0.83rem; color: #64748b; margin-bottom: 10px; }
        h1 { margin: 0 0 10px; color: #0b3b6e; line-height: 1.3; }
        .excerpt { font-size: 1.03rem; color: #334155; margin-bottom: 12px; }
        .author { display: flex; gap: 10px; align-items: center; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 12px 0; margin-bottom: 14px; }
        .author img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
        .author .avatar { width: 44px; height: 44px; border-radius: 50%; background: #0b3b6e; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .content h2 { color: #0b3b6e; margin-top: 30px; }
        .content h3 { color: #0b3b6e; margin-top: 24px; }
        .content h4 { color: #0b3b6e; margin-top: 20px; }
        .content p { line-height: 1.75; }
        .content img { max-width: 100%; border-radius: 10px; margin: 16px 0; }
        .content table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .content th, .content td { border: 1px solid #cbd5e1; padding: 8px; }
        .content th { background: #eff6ff; }
        .sidebar .card { background: #fff; border-radius: 12px; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08); padding: 14px; margin-bottom: 14px; }
        .sidebar h3 { margin: 0 0 10px; color: #0b3b6e; font-size: 1.05rem; }
        .toc-list { list-style: none; margin: 0; padding: 0; }
        .toc-list li { margin-bottom: 8px; }
        .toc-list a { text-decoration: none; color: #0f172a; font-size: 0.9rem; }
        .toc-list .h3 a { padding-left: 10px; display: inline-block; color: #334155; }
        .toc-list .h4 a { padding-left: 20px; display: inline-block; color: #475569; }
        .cta-form { background: linear-gradient(140deg, #032a54, #0b3b6e); color: #fff; border-radius: 12px; padding: 16px; margin: 26px 0; }
        .cta-form h3 { margin: 0 0 10px; color: #fff; }
        .cta-form .grid { display: grid; gap: 8px; }
        .cta-form input, .cta-form select { width: 100%; border: none; border-radius: 6px; padding: 10px; }
        .cta-form button { margin-top: 8px; border: none; width: 100%; border-radius: 8px; background: #f6b500; color: #0f172a; font-weight: 800; padding: 10px; cursor: pointer; }
        .faq { margin-top: 30px; }
        .faq details { border: 1px solid #dbeafe; border-radius: 10px; padding: 10px 12px; background: #f8fbff; margin-bottom: 8px; }
        .faq summary { cursor: pointer; font-weight: 700; color: #0b3b6e; }
        .success { margin-top: 8px; background: #dcfce7; color: #166534; border: 1px solid #86efac; border-radius: 8px; padding: 8px; font-size: 0.86rem; display: none; }
        .error { margin-top: 8px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 8px; padding: 8px; font-size: 0.86rem; display: none; }
        @media (max-width: 980px) { .layout { grid-template-columns: 1fr; } .hero-img { height: 260px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="top"><a href="/blog">Back to Blog</a></div>

        <div class="layout">
            <article class="article">
                <img class="hero-img" src="<?= h((string) $post['feature_image']) ?>" alt="<?= h((string) $post['feature_image_alt']) ?>" title="<?= h((string) ($post['feature_image_title'] ?? '')) ?>">
                <div class="inner">
                    <div class="meta"><?= h($primaryCategory) ?> | <?= h($publishDate) ?> | <?= h((string) $post['author_name']) ?></div>
                    <h1><?= h((string) $post['title']) ?></h1>
                    <p class="excerpt"><?= h((string) $post['excerpt']) ?></p>

                    <div class="author">
                        <?php if (!empty($post['author_image'])): ?>
                            <img src="<?= h((string) $post['author_image']) ?>" alt="<?= h((string) $post['author_name']) ?>">
                        <?php else: ?>
                            <div class="avatar"><?= h(strtoupper(substr((string) $post['author_name'], 0, 1))) ?></div>
                        <?php endif; ?>
                        <div>
                            <strong><?= h((string) $post['author_name']) ?></strong><br>
                            <small><?= h((string) ($post['author_bio'] ?? '')) ?></small>
                        </div>
                    </div>

                    <div class="content"><?= $contentHtml ?></div>

                    <section class="cta-form">
                        <h3><?= h($ctaHeading) ?></h3>
                        <form id="blogLeadForm">
                            <div class="grid">
                                <input type="text" name="name" placeholder="Full Name" required>
                                <input type="tel" name="phone" placeholder="10 digit mobile number" pattern="[0-9]{10}" required>
                                <select name="course" required>
                                    <option value="">Choose Course</option>
                                    <option value="Online MBA">Online MBA</option>
                                    <option value="Online BBA">Online BBA</option>
                                    <option value="Online MCA">Online MCA</option>
                                    <option value="Online BCA">Online BCA</option>
                                    <option value="Online MA">Online MA</option>
                                </select>
                            </div>
                            <input type="hidden" name="blog_post_id" value="<?= (int) $post['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <button type="submit"><?= h($ctaButton) ?></button>
                            <div class="success" id="leadSuccess">Thank you. We will contact you shortly.</div>
                            <div class="error" id="leadError">Unable to submit right now. Please try again.</div>
                        </form>
                    </section>

                    <?php if ($faq): ?>
                        <section class="faq">
                            <h2>Frequently Asked Questions</h2>
                            <?php foreach ($faq as $item): ?>
                                <?php if (!empty($item['q']) && !empty($item['a'])): ?>
                                    <details>
                                        <summary><?= h((string) $item['q']) ?></summary>
                                        <p><?= h((string) $item['a']) ?></p>
                                    </details>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>
                </div>
            </article>

            <aside class="sidebar">
                <?php if ($headings): ?>
                    <div class="card">
                        <h3>In This Article</h3>
                        <ul class="toc-list">
                            <?php foreach ($headings as $heading): ?>
                                <li class="<?= h((string) $heading['level']) ?>"><a href="#<?= h((string) $heading['id']) ?>"><?= h((string) $heading['title']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($post['tags'])): ?>
                    <div class="card">
                        <h3>Tags</h3>
                        <p><?= h((string) $post['tags']) ?></p>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>

    <script>
        document.getElementById('blogLeadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const success = document.getElementById('leadSuccess');
            const error = document.getElementById('leadError');
            success.style.display = 'none';
            error.style.display = 'none';

            const formData = new FormData(form);

            try {
                const res = await fetch('/blog-lead-submit.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    throw new Error('Request failed');
                }

                form.reset();
                success.style.display = 'block';
            } catch (err) {
                error.style.display = 'block';
            }
        });
    </script>
</body>
</html>
