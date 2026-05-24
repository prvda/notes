<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class NoteRepository
{
    public function paginateForUser(int $userId, string $query, int $page, int $perPage): array
    {
        $where = 'WHERE user_id = :user_id';
        $params = ['user_id' => $userId];

        if ($query !== '') {
            $where .= ' AND title LIKE :query';
            $params['query'] = '%' . $query . '%';
        }

        $pdo = Database::connection();
        $count = $pdo->prepare("SELECT COUNT(*) FROM notes {$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = max(0, ($page - 1) * $perPage);

        $statement = $pdo->prepare(
            "SELECT id, user_id, title, content, color, is_pinned, created_at, updated_at
             FROM notes
             {$where}
             ORDER BY is_pinned DESC, updated_at DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
        ];
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, user_id, title, content, color, is_pinned, created_at, updated_at
             FROM notes
             WHERE id = :id AND user_id = :user_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $note = $statement->fetch();

        return $note ?: null;
    }

    public function create(int $userId, array $values): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO notes (user_id, title, content, color)
             VALUES (:user_id, :title, :content, :color)'
        );
        $statement->execute([
            'user_id' => $userId,
            'title' => $values['title'],
            'content' => $values['content'],
            'color' => $values['color'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function updateForUser(int $id, int $userId, array $values): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE notes
             SET title = :title, content = :content, color = :color
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'title' => $values['title'],
            'content' => $values['content'],
            'color' => $values['color'],
        ]);
    }

    public function deleteForUser(int $id, int $userId): void
    {
        $statement = Database::connection()->prepare(
            'DELETE FROM notes WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function togglePinForUser(int $id, int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'UPDATE notes
             SET is_pinned = CASE WHEN is_pinned = 1 THEN 0 ELSE 1 END
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $this->findForUser($id, $userId);
    }
}

