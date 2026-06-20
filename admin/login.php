<?php

declare(strict_types=1);

require_once __DIR__ . '/../cms/functions.php';

ensure_cms_tables();

if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';
$setupMode = false;

$adminCount = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($adminCount === 0) {
    $setupMode = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request token. Please refresh and try again.';
    } elseif ($setupMode) {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($username === '' || strlen($username) < 4) {
            $error = 'Username must be at least 4 characters.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password confirmation does not match.';
        } else {
            $insert = db()->prepare('INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)');
            $insert->execute([
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $setupMode = false;
        }
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $stmt = db()->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, (string) $admin['password_hash'])) {
            $error = 'Invalid username or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = (string) $admin['username'];

            header('Location: /admin/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Amity Blog CMS</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(145deg, #f3f7fb 0%, #dbe8f7 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
            padding: 28px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 1.6rem;
            color: #0b3b6e;
        }

        p {
            margin-top: 0;
            color: #475569;
            font-size: 0.95rem;
        }

        .error {
            margin-bottom: 16px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 700;
            font-size: 0.92rem;
        }

        input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 11px 12px;
            font-size: 0.95rem;
            box-sizing: border-box;
        }

        button {
            margin-top: 18px;
            width: 100%;
            border: none;
            border-radius: 8px;
            background: #f6b500;
            color: #0f172a;
            font-weight: 800;
            padding: 12px;
            cursor: pointer;
            font-size: 0.95rem;
        }

        button:hover {
            background: #e9aa00;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1><?= $setupMode ? 'Create First Admin' : 'Admin Login' ?></h1>
        <p><?= $setupMode ? 'Set up your first CMS account. This screen appears only once.' : 'Sign in to manage blog posts and SEO metadata.' ?></p>

        <?php if ($error !== ''): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <?php if ($setupMode): ?>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            <?php endif; ?>

            <button type="submit"><?= $setupMode ? 'Create Admin' : 'Login' ?></button>
        </form>
    </main>
</body>
</html>
