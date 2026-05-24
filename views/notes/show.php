<?php

use App\Support\Csrf;

?>
<section class="page-header">
    <div>
        <h1 class="page-title"><?= e($note['title']) ?></h1>
        <p class="page-subtitle">Обновлено: <?= e($note['updated_at']) ?></p>
    </div>
    <div class="form-actions">
        <a class="button button--secondary" href="/notes">К списку</a>
        <a class="button" href="/notes/<?= e((string) $note['id']) ?>/edit">Изменить</a>
    </div>
</section>

<article class="note-detail" style="--note-color: <?= e($note['color']) ?>">
    <div class="note-detail__stripe"></div>
    <div class="note-detail__content">
        <?php if (trim((string) $note['content']) === ''): ?>
            <p class="note-detail__empty">Содержимое не заполнено.</p>
        <?php else: ?>
            <p><?= nl2br(e($note['content'])) ?></p>
        <?php endif; ?>
    </div>
    <footer class="note-detail__footer">
        <?php if ((int) $note['is_pinned'] === 1): ?>
            <span class="note-detail__pin"><i class="hgi-stroke hgi-pin icon" aria-hidden="true"></i> Закреплена</span>
        <?php endif; ?>
        <form action="/notes/<?= e((string) $note['id']) ?>/delete" method="post">
            <?= Csrf::field() ?>
            <button class="button button--danger" type="submit">Удалить</button>
        </form>
    </footer>
</article>
