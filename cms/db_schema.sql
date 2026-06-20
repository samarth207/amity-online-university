CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meta_title VARCHAR(60) NOT NULL,
    meta_description VARCHAR(250) NOT NULL,
    focus_keyword VARCHAR(180) NOT NULL,
    primary_keyword VARCHAR(180) DEFAULT NULL,
    title VARCHAR(70) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    excerpt VARCHAR(250) NOT NULL,
    content_html LONGTEXT NOT NULL,
    faq_json LONGTEXT DEFAULT NULL,
    cta_heading VARCHAR(150) DEFAULT NULL,
    cta_button_text VARCHAR(60) DEFAULT NULL,
    feature_image VARCHAR(255) NOT NULL,
    feature_image_alt VARCHAR(255) NOT NULL,
    feature_image_title VARCHAR(255) DEFAULT NULL,
    author_name VARCHAR(120) NOT NULL,
    author_bio TEXT DEFAULT NULL,
    author_image VARCHAR(255) DEFAULT NULL,
    author_page VARCHAR(255) DEFAULT NULL,
    categories_json VARCHAR(500) NOT NULL,
    tags VARCHAR(500) DEFAULT NULL,
    status ENUM('draft','pending','published','scheduled') NOT NULL DEFAULT 'draft',
    publish_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_publish_at (status, publish_at),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_leads (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    blog_post_id INT DEFAULT NULL,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(25) NOT NULL,
    course VARCHAR(100) NOT NULL,
    source_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_blog_post_id (blog_post_id),
    CONSTRAINT fk_blog_leads_post FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
