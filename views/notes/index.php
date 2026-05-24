<?php

use App\Support\Csrf;

$csrfToken = Csrf::token();
$queryString = static function (array $params) use ($query): string {
    $base = $query !== '' ? ['q' => $query] : [];

    return http_build_query(array_merge($base, $params));
};
?>
<section class="page-header">
    <div>
        <h1 class="page-title">Заметки</h1>
        <p class="page-subtitle">Всего заметок: <?= e((string) $total) ?></p>
    </div>
    <a class="button" href="/notes/create">Новая заметка</a>
</section>

<form class="search" action="/notes" method="get">
    <input type="search" name="q" value="<?= e($query) ?>" placeholder="Поиск по заголовку">
    <button class="button button--secondary" type="submit">Найти</button>
    <?php if ($query !== ''): ?>
        <a class="link-button" href="/notes">Сбросить</a>
    <?php endif; ?>
</form>

<?php if ($notes === []): ?>
    <section class="empty-state">
        <h2>Заметок пока нет</h2>
        <p>Создайте первую заметку или измените поисковый запрос.</p>
        <a class="button" href="/notes/create">Создать заметку</a>
    </section>
<?php else: ?>
    <section class="notes-grid">
        <?php foreach ($notes as $note): ?>
            <article
                class="note-card"
                :class="{ 'note-card--pinned': pinned }"
                :data-pinned="pinned ? '1' : '0'"
                x-data="noteCard(<?= e((string) $note['id']) ?>, <?= (int) $note['is_pinned'] === 1 ? 'true' : 'false' ?>, '<?= e($csrfToken) ?>')"
                data-pinned="<?= (int) $note['is_pinned'] === 1 ? '1' : '0' ?>"
                style="--note-color: <?= e($note['color']) ?>"
            >
                <div class="note-card__stripe"></div>
                <a class="note-card__overlay" href="/notes/<?= e((string) $note['id']) ?>" aria-label="Открыть заметку <?= e($note['title']) ?>"></a>
                <div class="note-card__header">
                    <h2 class="note-card__title"><?= e($note['title']) ?></h2>
                    <span class="note-card__pin" x-show="pinned" x-cloak title="Закреплена">
                        <i class="hgi-stroke hgi-pin icon" aria-hidden="true"></i>
                    </span>
                </div>
                <p class="note-card__content"><?= nl2br(e($note['content'])) ?></p>
                <div class="note-card__meta">Обновлено: <?= e($note['updated_at']) ?></div>
                <div class="note-card__actions">
                    <button
                        class="icon-button"
                        type="button"
                        @click="togglePin()"
                        :disabled="loading"
                        :aria-label="pinned ? 'Открепить' : 'Закрепить'"
                        :title="pinned ? 'Открепить' : 'Закрепить'"
                    >
                        <i class="hgi-stroke hgi-pin icon" aria-hidden="true"></i>
                    </button>
                    <a class="icon-button" href="/notes/<?= e((string) $note['id']) ?>/edit" aria-label="Изменить" title="Изменить">
                        <i class="hgi-stroke hgi-pencil-edit-02 icon" aria-hidden="true"></i>
                    </a>
                    <form action="/notes/<?= e((string) $note['id']) ?>/delete" method="post">
                        <?= Csrf::field() ?>
                        <button class="icon-button icon-button--danger" type="submit" aria-label="Удалить" title="Удалить">
                            <i class="hgi-stroke hgi-delete-02 icon" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
                <small class="card-error" x-show="error" x-text="error" x-cloak></small>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Пагинация">
        <?php if ($page > 1): ?>
            <a class="button button--secondary" href="/notes?<?= e($queryString(['page' => $page - 1])) ?>">Назад</a>
        <?php endif; ?>
        <span>Страница <?= e((string) $page) ?> из <?= e((string) $totalPages) ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="button button--secondary" href="/notes?<?= e($queryString(['page' => $page + 1])) ?>">Вперёд</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

