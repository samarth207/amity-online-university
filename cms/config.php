<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update these values with your Hostinger phpMyAdmin database credentials.
const DB_HOST = 'localhost';
const DB_NAME = 'u261758575_amity1';
const DB_USER = 'u261758575_amity1';
const DB_PASS = 'm3@G$HxmAr?C';

const SITE_URL = 'https://www.amityonlineadmission.in';
const SITE_NAME = 'Amity Online University';
const BLOG_UPLOAD_DIR = __DIR__ . '/../uploads/blog';
const BLOG_UPLOAD_URL = '/uploads/blog';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
