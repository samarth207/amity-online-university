<?php

declare(strict_types=1);

require_once __DIR__ . '/../cms/functions.php';

ensure_cms_tables();
require_admin();

$sql = 'SELECT id, title, slug, status, publish_at, updated_at FROM blog_posts ORDER BY updated_at DESC';
$posts = db()->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog CMS Dashboard</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6f8fb; color: #1e293b; }
        .topbar { background: #0b3b6e; color: #fff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; }
        .topbar a { color: #fff; text-decoration: none; font-weight: 700; }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 18px; }
        .actions { margin-bottom: 16px; display: flex; gap: 10px; }
        .btn { display: inline-block; text-decoration: none; border-radius: 8px; padding: 10px 14px; font-weight: 700; }
        .btn-primary { background: #f6b500; color: #0f172a; }
        .btn-secondary { background: #e2e8f0; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.92rem; vertical-align: top; }
        th { background: #f8fafc; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.03em; color: #475569; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-draft { background: #e2e8f0; color: #334155; }
        .status-pending { background: #fff7ed; color: #9a3412; }
        .status-published { background: #dcfce7; color: #166534; }
        .status-scheduled { background: #dbeafe; color: #1d4ed8; }
        .inline-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .inline-actions a { text-decoration: none; font-size: 0.85rem; color: #0b3b6e; font-weight: 700; }
        .inline-actions form { margin: 0; }
        .inline-actions button { border: none; background: transparent; color: #b91c1c; cursor: pointer; font-size: 0.85rem; font-weight: 700; }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Amity Blog CMS</strong>
        <div>
            <span style="margin-right: 14px;">Signed in: <?= h((string) ($_SESSION['admin_username'] ?? 'admin')) ?></span>
            <a href="/admin/logout.php">Logout</a>
        </div>
    </header>

    <main class="container">
        <div class="actions">
            <a class="btn btn-primary" href="/admin/post-editor.php">Create New Blog</a>
            <a class="btn btn-secondary" href="/blog">View Blog Page</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Publish Date</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$posts): ?>
                    <tr>
                        <td colspan="6">No blog posts created yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= h((string) $post['title']) ?></td>
                            <td><?= h((string) $post['slug']) ?></td>
                            <td>
                                <span class="status status-<?= h((string) $post['status']) ?>"><?= h((string) $post['status']) ?></span>
                            </td>
                            <td><?= h((string) ($post['publish_at'] ?? '-')) ?></td>
                            <td><?= h((string) $post['updated_at']) ?></td>
                            <td>
                                <div class="inline-actions">
                                    <a href="/admin/post-editor.php?id=<?= (int) $post['id'] ?>">Edit</a>
                                    <a href="/blog/<?= h((string) $post['slug']) ?>" target="_blank" rel="noopener">Preview</a>
                                    <form method="post" action="/admin/post-delete.php" onsubmit="return confirm('Delete this post?');">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
