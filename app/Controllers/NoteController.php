<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\NoteRepository;
use App\Support\Auth;
use App\Support\Csrf;

final class NoteController
{
    private const PER_PAGE = 20;

    private NoteRepository $notes;

    public function __construct()
    {
        Auth::requireUser();
        $this->notes = new NoteRepository();
    }

    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->notes->paginateForUser(Auth::id(), $query, $page, self::PER_PAGE);
        $totalPages = max(1, (int) ceil($result['total'] / self::PER_PAGE));

        View::render('notes/index', [
            'title' => 'Заметки',
            'notes' => $result['items'],
            'query' => $query,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $result['total'],
        ]);
    }

    public function create(): void
    {
        $errors = errors();
        View::render('notes/form', [
            'title' => 'Новая заметка',
            'heading' => 'Новая заметка',
            'action' => '/notes',
            'note' => [
                'title' => old('title'),
                'content' => old('content'),
                'color' => old('color', '#6366f1'),
            ],
            'errors' => $errors,
        ]);
        clear_old_input();
    }

    public function show(string $id): void
    {
        $note = $this->findOwnNote($id);

        if (!$note) {
            $this->notFound();
            return;
        }

        View::render('notes/show', [
            'title' => $note['title'],
            'note' => $note,
        ]);
    }

    public function store(): void
    {
        Csrf::requireValid();
        $data = $this->validatedData();

        if ($data['errors'] !== []) {
            back_with_errors($data['errors'], $data['old'], '/notes/create');
        }

        $this->notes->create(Auth::id(), $data['values']);
        flash('success', 'Заметка создана.');
        redirect('/notes');
    }

    public function edit(string $id): void
    {
        $note = $this->findOwnNote($id);

        if (!$note) {
            $this->notFound();
            return;
        }

        $errors = errors();
        View::render('notes/form', [
            'title' => 'Редактирование заметки',
            'heading' => 'Редактирование заметки',
            'action' => '/notes/' . (int) $id,
            'note' => [
                'title' => old('title', $note['title']),
                'content' => old('content', $note['content']),
                'color' => old('color', $note['color']),
            ],
            'errors' => $errors,
        ]);
        clear_old_input();
    }

    public function update(string $id): void
    {
        Csrf::requireValid();

        if (!$this->findOwnNote($id)) {
            $this->notFound();
            return;
        }

        $data = $this->validatedData();

        if ($data['errors'] !== []) {
            back_with_errors($data['errors'], $data['old'], '/notes/' . (int) $id . '/edit');
        }

        $this->notes->updateForUser((int) $id, Auth::id(), $data['values']);
        flash('success', 'Заметка обновлена.');
        redirect('/notes');
    }

    public function destroy(string $id): void
    {
        Csrf::requireValid();
        $this->notes->deleteForUser((int) $id, Auth::id());
        flash('success', 'Заметка удалена.');
        redirect('/notes');
    }

    public function togglePin(string $id): void
    {
        Csrf::requireValid();
        $note = $this->notes->togglePinForUser((int) $id, Auth::id());

        if (!$note) {
            http_response_code(404);
            $this->json(['message' => 'Заметка не найдена.']);
            return;
        }

        $this->json([
            'id' => (int) $note['id'],
            'is_pinned' => (bool) $note['is_pinned'],
        ]);
    }

    private function validatedData(): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $color = trim((string) ($_POST['color'] ?? '#6366f1'));
        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Укажите заголовок.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Заголовок должен быть не длиннее 255 символов.';
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $errors['color'] = 'Выберите корректный hex-цвет.';
        }

        return [
            'errors' => $errors,
            'values' => [
                'title' => $title,
                'content' => $content,
                'color' => strtolower($color),
            ],
            'old' => [
                'title' => $title,
                'content' => $content,
                'color' => $color,
            ],
        ];
    }

    private function findOwnNote(string $id): ?array
    {
        return $this->notes->findForUser((int) $id, Auth::id());
    }

    private function notFound(): void
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Заметка не найдена']);
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}

