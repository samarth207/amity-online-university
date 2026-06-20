<?php

declare(strict_types=1);

require_once __DIR__ . '/../cms/functions.php';

ensure_cms_tables();
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = null;

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();
}

$categories = [
    'Education',
    'Online MBA',
    'Admissions',
    'Career Guidance',
    'University Comparison',
    'Scholarships',
    'Exam Preparation',
    'Student Stories',
];

$selectedCategories = [];
if ($post && !empty($post['categories_json'])) {
    $selectedCategories = json_decode((string) $post['categories_json'], true) ?: [];
}

$faqItems = [];
if ($post && !empty($post['faq_json'])) {
    $faqItems = json_decode((string) $post['faq_json'], true) ?: [];
}

$publishDate = '';
$publishTime = '';
if ($post && !empty($post['publish_at'])) {
    $dt = strtotime((string) $post['publish_at']);
    if ($dt !== false) {
        $publishDate = date('Y-m-d', $dt);
        $publishTime = date('H:i', $dt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? 'Edit Blog' : 'Create Blog' ?> | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6f8fb; color: #0f172a; }
        .topbar { background: #0b3b6e; color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; }
        .topbar a { color: #fff; text-decoration: none; font-weight: 700; }
        .container { max-width: 1160px; margin: 20px auto; padding: 0 16px 40px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08); padding: 18px; margin-bottom: 14px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .full { grid-column: 1 / -1; }
        label { display: block; font-weight: 700; font-size: 0.88rem; margin-bottom: 6px; }
        input[type="text"], input[type="url"], input[type="date"], input[type="time"], select, textarea {
            width: 100%; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-size: 0.92rem;
        }
        textarea { min-height: 96px; resize: vertical; }
        .hint { margin-top: 4px; font-size: 0.75rem; color: #475569; }
        .counter { float: right; font-size: 0.74rem; color: #64748b; }
        .section-title { margin: 0 0 12px; font-size: 1.05rem; color: #0b3b6e; }
        .faq-item { border: 1px dashed #cbd5e1; padding: 10px; border-radius: 10px; margin-bottom: 10px; }
        .categories { display: flex; flex-wrap: wrap; gap: 8px; }
        .categories label { font-weight: 500; background: #f1f5f9; padding: 6px 10px; border-radius: 999px; margin: 0; }
        .actions { display: flex; gap: 10px; align-items: center; margin-top: 12px; }
        .cms-btn { border: none; border-radius: 8px; padding: 10px 14px; font-weight: 700; cursor: pointer; display: inline-block; }
        .cms-btn-primary { background: #f6b500; color: #0f172a; }
        .cms-btn-muted { background: #e2e8f0; color: #0f172a; text-decoration: none; }
        .note-editor.note-frame { border: 1px solid #cbd5e1; border-radius: 8px; }
        .note-toolbar { border-bottom: 1px solid #e2e8f0; }
        .note-editor .note-toolbar { position: sticky; top: 10px; z-index: 15; background: #ffffff; }
        .note-editing-area { max-height: 560px; overflow-y: auto; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Amity Blog CMS</strong>
        <a href="/admin/index.php">Back to Dashboard</a>
    </header>

    <main class="container">
        <form action="/admin/post-save.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) ($post['id'] ?? 0) ?>">

            <section class="card">
                <h2 class="section-title">1. Meta Data</h2>
                <div class="grid">
                    <div>
                        <label for="meta_title">Meta Title <span class="counter" id="metaTitleCount">0/60</span></label>
                        <input type="text" id="meta_title" name="meta_title" maxlength="60" required value="<?= h((string) ($post['meta_title'] ?? '')) ?>">
                    </div>
                    <div>
                        <label for="focus_keyword">Focus Keyword</label>
                        <input type="text" id="focus_keyword" name="focus_keyword" required value="<?= h((string) ($post['focus_keyword'] ?? '')) ?>">
                    </div>
                    <div class="full">
                        <label for="meta_description">Meta Description <span class="counter" id="metaDescriptionCount">0/250</span></label>
                        <textarea id="meta_description" name="meta_description" maxlength="250" required><?= h((string) ($post['meta_description'] ?? '')) ?></textarea>
                        <div class="hint">Recommended length: 200-250 characters</div>
                    </div>
                    <div>
                        <label for="primary_keyword">Primary Keyword</label>
                        <input type="text" id="primary_keyword" name="primary_keyword" value="<?= h((string) ($post['primary_keyword'] ?? '')) ?>">
                    </div>
                    <div>
                        <label for="slug">URL Slug</label>
                        <input type="text" id="slug" name="slug" required value="<?= h((string) ($post['slug'] ?? '')) ?>" pattern="[a-z0-9-]+">
                        <div class="hint">Lowercase with hyphen, example: amity-online-mba-fees-2026</div>
                    </div>
                </div>
            </section>

            <section class="card">
                <h2 class="section-title">2. Feature Image</h2>
                <div class="grid">
                    <div>
                        <label for="feature_image">Feature Image Upload (JPG/WebP, 1200x628 recommended)</label>
                        <input type="file" id="feature_image" name="feature_image" accept="image/jpeg,image/webp" <?= $post ? '' : 'required' ?>>
                        <?php if ($post && !empty($post['feature_image'])): ?>
                            <div class="hint">Current: <?= h((string) $post['feature_image']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="feature_image_alt">Image Alt Text</label>
                        <input type="text" id="feature_image_alt" name="feature_image_alt" required value="<?= h((string) ($post['feature_image_alt'] ?? '')) ?>">
                    </div>
                    <div class="full">
                        <label for="feature_image_title">Image Title (optional)</label>
                        <input type="text" id="feature_image_title" name="feature_image_title" value="<?= h((string) ($post['feature_image_title'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="card">
                <h2 class="section-title">3. Blog Content</h2>
                <div class="grid">
                    <div class="full">
                        <label for="title">Blog Title (H1) <span class="counter" id="titleCount">0/70</span></label>
                        <input type="text" id="title" name="title" maxlength="70" required value="<?= h((string) ($post['title'] ?? '')) ?>">
                    </div>
                    <div class="full">
                        <label for="excerpt">Short Description / Excerpt <span class="counter" id="excerptCount">0/250</span></label>
                        <textarea id="excerpt" name="excerpt" maxlength="250" required><?= h((string) ($post['excerpt'] ?? '')) ?></textarea>
                    </div>
                    <div class="full">
                        <label for="content_html">Content Editor (H2/H3/H4, links, image, tables, FAQ block support)</label>
                        <textarea id="content_html" name="content_html" required><?= h((string) ($post['content_html'] ?? '')) ?></textarea>
                    </div>
                </div>
            </section>

            <section class="card">
                <h2 class="section-title">FAQ Section Block</h2>
                <div id="faqContainer">
                    <?php if (!$faqItems): ?>
                        <div class="faq-item">
                            <label>Question</label>
                            <input type="text" name="faq_question[]" value="">
                            <label>Answer</label>
                            <textarea name="faq_answer[]"></textarea>
                        </div>
                    <?php else: ?>
                        <?php foreach ($faqItems as $faq): ?>
                            <div class="faq-item">
                                <label>Question</label>
                                <input type="text" name="faq_question[]" value="<?= h((string) ($faq['q'] ?? '')) ?>">
                                <label>Answer</label>
                                <textarea name="faq_answer[]"><?= h((string) ($faq['a'] ?? '')) ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button class="cms-btn cms-btn-muted" type="button" id="addFaq">Add FAQ</button>
            </section>

            <section class="card">
                <h2 class="section-title">Inline Lead Form Block</h2>
                <div class="grid">
                    <div>
                        <label for="cta_heading">Custom Headline</label>
                        <input type="text" id="cta_heading" name="cta_heading" value="<?= h((string) ($post['cta_heading'] ?? 'Your Future Starts Here')) ?>">
                    </div>
                    <div>
                        <label for="cta_button_text">Button Text</label>
                        <input type="text" id="cta_button_text" name="cta_button_text" value="<?= h((string) ($post['cta_button_text'] ?? 'Submit')) ?>">
                    </div>
                </div>
                <div class="hint">Fixed fields shown in blog: Name, Number, Course (matching existing website lead form style).</div>
            </section>

            <section class="card">
                <h2 class="section-title">5. Author Section</h2>
                <div class="grid">
                    <div>
                        <label for="author_name">Author Name</label>
                        <input type="text" id="author_name" name="author_name" required value="<?= h((string) ($post['author_name'] ?? 'Content Team')) ?>">
                    </div>
                    <div>
                        <label for="author_page">Author Page (optional)</label>
                        <input type="url" id="author_page" name="author_page" value="<?= h((string) ($post['author_page'] ?? '')) ?>">
                    </div>
                    <div class="full">
                        <label for="author_bio">Author Bio (optional)</label>
                        <textarea id="author_bio" name="author_bio"><?= h((string) ($post['author_bio'] ?? '')) ?></textarea>
                    </div>
                    <div>
                        <label for="author_image">Author Image (optional)</label>
                        <input type="file" id="author_image" name="author_image" accept="image/jpeg,image/png,image/webp">
                        <?php if ($post && !empty($post['author_image'])): ?>
                            <div class="hint">Current: <?= h((string) $post['author_image']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="card">
                <h2 class="section-title">6. Category & Tags</h2>
                <div class="grid">
                    <div class="full">
                        <label>Category (multi-select, at least one)</label>
                        <div class="categories">
                            <?php foreach ($categories as $category): ?>
                                <label>
                                    <input type="checkbox" name="categories[]" value="<?= h($category) ?>" <?= in_array($category, $selectedCategories, true) ? 'checked' : '' ?>>
                                    <?= h($category) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="full">
                        <label for="tags">Tags (comma separated)</label>
                        <input type="text" id="tags" name="tags" value="<?= h((string) ($post['tags'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="card">
                <h2 class="section-title">8. Blog Scheduling</h2>
                <div class="grid">
                    <div>
                        <label for="status">Publish Status</label>
                        <select id="status" name="status" required>
                            <?php
                            $status = (string) ($post['status'] ?? 'draft');
                            $statuses = ['draft', 'pending', 'published', 'scheduled'];
                            foreach ($statuses as $item):
                            ?>
                                <option value="<?= h($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= ucfirst($item) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="publish_date">Publish Date</label>
                        <input type="date" id="publish_date" name="publish_date" value="<?= h($publishDate) ?>">
                    </div>
                    <div>
                        <label for="publish_time">Publish Time</label>
                        <input type="time" id="publish_time" name="publish_time" value="<?= h($publishTime) ?>">
                    </div>
                    <div>
                        <label>Last Updated Date</label>
                        <input type="text" disabled value="<?= h((string) ($post['updated_at'] ?? 'Auto on save')) ?>">
                    </div>
                </div>
            </section>

            <div class="actions">
                <button class="cms-btn cms-btn-primary" type="submit">Save Blog Post</button>
                <a class="cms-btn cms-btn-muted" href="/admin/index.php">Cancel</a>
            </div>
        </form>
    </main>

    <script>
        const toSlug = (value) => value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');

        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        let slugTouched = slugInput.value.trim() !== '';

        slugInput.addEventListener('input', () => {
            slugTouched = true;
            slugInput.value = toSlug(slugInput.value);
        });

        titleInput.addEventListener('input', () => {
            if (!slugTouched) {
                slugInput.value = toSlug(titleInput.value);
            }
        });

        function wireCounter(inputId, countId, max) {
            const input = document.getElementById(inputId);
            const count = document.getElementById(countId);
            const update = () => {
                count.textContent = `${input.value.length}/${max}`;
            };
            input.addEventListener('input', update);
            update();
        }

        wireCounter('meta_title', 'metaTitleCount', 60);
        wireCounter('meta_description', 'metaDescriptionCount', 250);
        wireCounter('title', 'titleCount', 70);
        wireCounter('excerpt', 'excerptCount', 250);

        document.getElementById('addFaq').addEventListener('click', () => {
            const box = document.createElement('div');
            box.className = 'faq-item';
            box.innerHTML = `
                <label>Question</label>
                <input type="text" name="faq_question[]" value="">
                <label>Answer</label>
                <textarea name="faq_answer[]"></textarea>
            `;
            document.getElementById('faqContainer').appendChild(box);
        });

        $('#content_html').summernote({
            height: 540,
            dialogsInBody: true,
            placeholder: 'Write SEO-friendly blog content with H2/H3/H4, links, tables and images...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'table']],
                ['view', ['codeview', 'help']]
            ],
            styleTags: [
                'p',
                { title: 'H2', tag: 'h2', value: 'h2' },
                { title: 'H3', tag: 'h3', value: 'h3' },
                { title: 'H4', tag: 'h4', value: 'h4' }
            ],
            callbacks: {
                onInit: function() {
                    const editor = $(this).next('.note-editor');
                    editor.find('.note-toolbar').css({
                        position: 'sticky',
                        top: '10px',
                        zIndex: '20',
                        background: '#fff'
                    });
                },
                onImageUpload: function(files) {
                    for (let i = 0; i < files.length; i++) {
                        uploadEditorImage(files[i]);
                    }
                }
            }
        });

        function uploadEditorImage(file) {
            const formData = new FormData();
            formData.append('csrf_token', '<?= h(csrf_token()) ?>');
            formData.append('file', file);

            fetch('/admin/media-upload.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Image upload failed');
                }
                return response.json();
            })
            .then((json) => {
                if (!json || typeof json.location !== 'string') {
                    throw new Error('Invalid upload response');
                }

                const image = document.createElement('img');
                image.src = json.location;
                image.alt = 'Add descriptive alt text';
                image.title = '';
                $('#content_html').summernote('insertNode', image);
            })
            .catch(() => {
                alert('Image upload failed. Please try again.');
            });
        }
    </script>
</body>
</html>
