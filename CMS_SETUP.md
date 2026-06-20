# Blog CMS Setup (PHP + phpMyAdmin)

This project now includes a PHP-based Blog CMS with admin login, SEO fields, scheduling, and automatic publishing in the blog section.

## 1. Hostinger / phpMyAdmin Database

1. Create a MySQL database in Hostinger.
2. Create a database user and assign full permissions.
3. Open `cms/config.php` and update:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
4. Keep `SITE_URL` as your production domain.

## 2. First Admin Login

1. Visit `/admin/login.php`.
2. On first run, CMS auto-creates required tables from `cms/db_schema.sql`.
3. You will see the one-time "Create First Admin" screen.
4. Create username + password, then login.

## 3. Create and Publish Blog Posts

1. Open `/admin/index.php`.
2. Click "Create New Blog".
3. Fill all required SEO fields:
   - Meta Title (<=60)
   - Meta Description (200-250)
   - Focus Keyword
   - URL Slug
   - Feature image + alt text
4. Write blog body in rich editor (supports H2/H3/H4, links, image, table, list).
5. Add FAQ entries and inline lead-form block heading/button text.
6. Choose categories and tags.
7. Set status:
   - `Draft`
   - `Pending`
   - `Published`
   - `Scheduled` + date/time

## 4. Public URLs

- Blog listing: `/blog`
- Blog details: `/blog/{slug}`

`.htaccess` routes these URLs to:
- `blog.php`
- `blog-post.php`

## 5. SEO Features Included

- Editable meta title/description/keywords
- Canonical URL per blog
- Open Graph + Twitter tags
- BlogPosting structured data (JSON-LD)
- FAQ structured data when FAQs are added
- Mandatory feature image alt text
- TOC (index-like section) auto-built from H2/H3/H4 in content
- SEO-friendly slug generation and uniqueness handling

## 6. Blog Lead Form Storage

Blog inline lead form stores Name, Number, Course in `blog_leads` table via `blog-lead-submit.php`.

## 7. Important Notes

- Upload path: `/uploads/blog`
- Allowed image formats: JPG, PNG, WebP
- If local machine has no PHP installed, syntax checks must be done on server/hosting environment.
