<?php

declare(strict_types=1);

require_once __DIR__ . '/cms/functions.php';

ensure_cms_tables();
regenerate_sitemap_if_stale(1800);

$sql = "SELECT id, title, slug, excerpt, feature_image, feature_image_alt, author_name, publish_at, categories_json
        FROM blog_posts
        WHERE status = 'published'
           OR (status = 'scheduled' AND publish_at IS NOT NULL AND publish_at <= NOW())
        ORDER BY COALESCE(publish_at, created_at) DESC";
$posts = db()->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Amity Online University</title>
    <meta name="description" content="Explore insights on online education, admissions, scholarships, and career growth at Amity Online University.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?= h(SITE_URL) ?>/blog">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Blog | Amity Online University">
    <meta property="og:description" content="Latest posts on online MBA, admissions, and career guidance.">
    <meta property="og:url" content="<?= h(SITE_URL) ?>/blog">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #1e293b; }
        .hero { background: linear-gradient(125deg, #032a54, #0b3b6e); color: #fff; padding: 56px 20px; text-align: center; }
        .hero h1 { margin: 0 0 10px; font-size: 2.4rem; }
        .hero p { margin: 0; opacity: 0.9; }
        .container { max-width: 1180px; margin: 24px auto; padding: 0 16px 50px; }
        .topnav { margin-bottom: 18px; }
        .topnav a { color: #0b3b6e; text-decoration: none; font-weight: 700; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; }
        .card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08); }
        .card img { width: 100%; height: 210px; object-fit: cover; }
        .card-body { padding: 15px; }
        .meta { color: #64748b; font-size: 0.82rem; margin-bottom: 7px; }
        .title { margin: 0 0 10px; font-size: 1.16rem; line-height: 1.4; }
        .title a { color: #0f172a; text-decoration: none; }
        .title a:hover { color: #0b3b6e; }
        .excerpt { margin: 0 0 12px; color: #334155; font-size: 0.93rem; }
        .readmore { font-weight: 700; color: #0b3b6e; text-decoration: none; }
        .empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; }
    </style>
</head>
<body>
    <section class="hero">
        <h1>Amity Blog</h1>
        <p>Career insights, admission guidance, and online MBA deep dives</p>
    </section>

    <main class="container">
        <div class="topnav"><a href="/">Back to Home</a></div>

        <?php if (!$posts): ?>
            <div class="empty">No published posts yet.</div>
        <?php else: ?>
            <section class="grid">
                <?php foreach ($posts as $post): ?>
                    <?php
                    $categories = json_decode((string) $post['categories_json'], true) ?: [];
                    $primaryCategory = $categories[0] ?? 'Education';
                    $publishDate = !empty($post['publish_at']) ? date('F j, Y', strtotime((string) $post['publish_at'])) : date('F j, Y');
                    ?>
                    <article class="card">
                        <a href="/blog/<?= h((string) $post['slug']) ?>">
                            <img src="<?= h((string) $post['feature_image']) ?>" alt="<?= h((string) $post['feature_image_alt']) ?>" loading="lazy">
                        </a>
                        <div class="card-body">
                            <div class="meta"><?= h($primaryCategory) ?> | <?= h($publishDate) ?> | By <?= h((string) $post['author_name']) ?></div>
                            <h2 class="title"><a href="/blog/<?= h((string) $post['slug']) ?>"><?= h((string) $post['title']) ?></a></h2>
                            <p class="excerpt"><?= h((string) $post['excerpt']) ?></p>
                            <a class="readmore" href="/blog/<?= h((string) $post['slug']) ?>">Read Article</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
