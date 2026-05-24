<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;

$database = (string) Config::get('DB_DATABASE', 'notes_app');

if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
    fwrite(STDERR, "Invalid database name.\n");
    exit(1);
}

$host = (string) Config::get('DB_HOST', '127.0.0.1');
$port = (string) Config::get('DB_PORT', '3306');
$username = (string) Config::get('DB_USERNAME', 'root');
$password = (string) Config::get('DB_PASSWORD', '');

$pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$database}`");
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
$applied = array_flip($applied ?: []);
$migrationFiles = glob(base_path('database/migrations/*.sql')) ?: [];
sort($migrationFiles);

foreach ($migrationFiles as $file) {
    $name = basename($file);

    if (isset($applied[$name])) {
        echo "Skipped {$name}\n";
        continue;
    }

    $sql = file_get_contents($file);

    if ($sql === false) {
        fwrite(STDERR, "Cannot read {$name}\n");
        exit(1);
    }

    foreach (preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [] as $statement) {
        $statement = trim($statement);

        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }

    $record = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
    $record->execute(['migration' => $name]);
    echo "Applied {$name}\n";
}

if (in_array('--seed', $argv, true)) {
    $seedUser = static function (PDO $pdo, string $name, string $email): int {
        $statement = $pdo->prepare(
            'INSERT INTO users (name, email, password)
             SELECT :name, :email, :password
             WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = :email_check)'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'email_check' => $email,
        ]);

        echo ($statement->rowCount() > 0 ? 'Seeded' : 'Already exists') . " user: {$email} / password\n";

        $lookup = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $lookup->execute(['email' => $email]);

        return (int) $lookup->fetchColumn();
    };

    $seedNote = static function (PDO $pdo, int $userId, string $title, string $content, string $color): void {
        $statement = $pdo->prepare(
            'INSERT INTO notes (user_id, title, content, color)
             SELECT :user_id, :title, :content, :color
             WHERE NOT EXISTS (
                SELECT 1 FROM notes WHERE user_id = :user_id_check AND title = :title_check
             )'
        );
        $statement->execute([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'color' => $color,
            'user_id_check' => $userId,
            'title_check' => $title,
        ]);
    };

    $demoUserId = $seedUser($pdo, 'Demo User', 'demo@example.com');
    $secondUserId = $seedUser($pdo, 'Second Demo User', 'second@example.com');

    $seedNote($pdo, $demoUserId, 'Demo user note', 'This note belongs to demo@example.com.', '#6366f1');
    $seedNote($pdo, $secondUserId, 'Second user private note', 'This note belongs to second@example.com.', '#10b981');

    echo "Seeded demo notes for ownership testing.\n";
}
