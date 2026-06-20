<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function ensure_cms_tables(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $stmt = db()->query("SHOW TABLES LIKE 'blog_posts'");
    $exists = (bool) $stmt->fetchColumn();

    if (!$exists) {
        $schema = file_get_contents(__DIR__ . '/db_schema.sql');
        if (is_string($schema) && trim($schema) !== '') {
            foreach (explode(';', $schema) as $query) {
                $query = trim($query);
                if ($query !== '') {
                    db()->exec($query);
                }
            }
        }
    }

    $initialized = true;
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? '';
    $text = preg_replace('/\s+/', '-', $text) ?? '';
    $text = preg_replace('/-+/', '-', $text) ?? '';

    return trim($text, '-') ?: 'blog-post';
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function post_status_resolved(string $status, ?string $publishAt): string
{
    if ($status !== 'scheduled') {
        return $status;
    }

    if (!$publishAt) {
        return 'scheduled';
    }

    $publishTime = strtotime($publishAt);
    if ($publishTime !== false && $publishTime <= time()) {
        return 'published';
    }

    return 'scheduled';
}

function build_publish_at(?string $date, ?string $time): ?string
{
    if (!$date) {
        return null;
    }

    $time = $time ?: '00:00';
    $dateTime = $date . ' ' . $time . ':00';

    $timestamp = strtotime($dateTime);
    if ($timestamp === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function normalize_tags(string $tags): string
{
    $parts = array_filter(array_map('trim', explode(',', $tags)));
    $parts = array_values(array_unique($parts));

    return implode(', ', $parts);
}

function current_full_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? parse_url(SITE_URL, PHP_URL_HOST);
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return $scheme . '://' . $host . $uri;
}

function parse_content_headings(string $html): array
{
    if (trim($html) === '') {
        return ['html' => $html, 'headings' => []];
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $headings = [];
    $idx = 1;
    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query('//h2|//h3|//h4');

    if ($nodes) {
        foreach ($nodes as $node) {
            if (!($node instanceof DOMElement)) {
                continue;
            }

            /** @var DOMElement $element */
            $element = $node;

            $title = trim($element->textContent);
            if ($title === '') {
                continue;
            }

            $id = slugify($title) . '-' . $idx;
            $element->setAttribute('id', $id);

            $headings[] = [
                'level' => strtolower($element->tagName),
                'title' => $title,
                'id' => $id,
            ];
            $idx++;
        }
    }

    $finalHtml = $doc->saveHTML() ?: $html;

    return [
        'html' => $finalHtml,
        'headings' => $headings,
    ];
}

function sanitize_rich_html(string $html): string
{
    // Keep formatting tags needed for content/SEO while stripping scripts and inline JS.
    $allowed = '<p><a><ul><ol><li><strong><b><em><i><h2><h3><h4><blockquote><table><thead><tbody><tr><th><td><img><figure><figcaption><br><hr><span><div>';
    $clean = strip_tags($html, $allowed);

    $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $clean) ?? $clean;
    $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean) ?? $clean;
    $clean = preg_replace('/\son\w+=\'[^\']*\'/i', '', $clean) ?? $clean;

    return $clean;
}

function fetch_blog_post_by_slug(string $slug): ?array
{
    $sql = "SELECT * FROM blog_posts WHERE slug = :slug LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute(['slug' => $slug]);
    $post = $stmt->fetch();

    if (!$post) {
        return null;
    }

    $resolved = post_status_resolved($post['status'], $post['publish_at']);
    if ($resolved !== $post['status']) {
        $update = db()->prepare('UPDATE blog_posts SET status = :status WHERE id = :id');
        $update->execute(['status' => $resolved, 'id' => $post['id']]);
        $post['status'] = $resolved;
        regenerate_sitemap();
    }

    return $post;
}

function regenerate_sitemap(): bool
{
    $staticUrls = [
        [
            'loc' => SITE_URL . '/',
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ],
        [
            'loc' => SITE_URL . '/application-form',
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.9',
        ],
        [
            'loc' => SITE_URL . '/blog',
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ],
    ];

    $sql = "SELECT slug, publish_at, updated_at, created_at
            FROM blog_posts
            WHERE status = 'published'
               OR (status = 'scheduled' AND publish_at IS NOT NULL AND publish_at <= NOW())
            ORDER BY COALESCE(publish_at, created_at) DESC";
    $posts = db()->query($sql)->fetchAll();

    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->setIndent(true);

    $xml->startElement('urlset');
    $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    foreach ($staticUrls as $entry) {
        $xml->startElement('url');
        $xml->writeElement('loc', $entry['loc']);
        $xml->writeElement('lastmod', $entry['lastmod']);
        $xml->writeElement('changefreq', $entry['changefreq']);
        $xml->writeElement('priority', $entry['priority']);
        $xml->endElement();
    }

    foreach ($posts as $post) {
        $lastmodSource = $post['updated_at'] ?: ($post['publish_at'] ?: $post['created_at']);
        $lastmod = date('Y-m-d', strtotime((string) $lastmodSource));

        $xml->startElement('url');
        $xml->writeElement('loc', SITE_URL . '/blog/' . $post['slug']);
        $xml->writeElement('lastmod', $lastmod);
        $xml->writeElement('changefreq', 'monthly');
        $xml->writeElement('priority', '0.7');
        $xml->endElement();
    }

    $xml->endElement();
    $xml->endDocument();

    $sitemapPath = dirname(__DIR__) . '/sitemap.xml';
    $tmpPath = $sitemapPath . '.tmp';
    $written = file_put_contents($tmpPath, $xml->outputMemory());
    if ($written === false) {
        return false;
    }

    return rename($tmpPath, $sitemapPath);
}

function regenerate_sitemap_if_stale(int $maxAgeSeconds = 1800): bool
{
    $sitemapPath = dirname(__DIR__) . '/sitemap.xml';

    if (!is_file($sitemapPath)) {
        return regenerate_sitemap();
    }

    $lastModified = filemtime($sitemapPath);
    if ($lastModified === false) {
        return regenerate_sitemap();
    }

    if ((time() - $lastModified) < $maxAgeSeconds) {
        return true;
    }

    return regenerate_sitemap();
}
