<?php

use App\Support\Csrf;

$title = $title ?? 'Notes';
$user = $_SESSION['user'] ?? null;
$flashMessage = flash('success');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%234f46e5'/%3E%3Cpath d='M10 9h12v2H10zM10 15h12v2H10zM10 21h8v2h-8z' fill='white'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke-rounded.css">
    <link rel="stylesheet" href="/assets/app.css">
    <script defer src="/assets/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <div class="topbar__inner">
                <a class="brand" href="/notes">Notes</a>
                <div class="topbar__actions">
                    <?php if ($user): ?>
                        <span><?= e($user['name']) ?></span>
                        <form action="/logout" method="post">
                            <?= Csrf::field() ?>
                            <button class="link-button" type="submit">Выйти</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="page">
            <?php if ($flashMessage): ?>
                <div class="flash"><?= e($flashMessage) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</body>
</html>

