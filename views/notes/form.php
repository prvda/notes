<?php

use App\Support\Csrf;

$errors = $errors ?? [];
$note = $note ?? ['title' => '', 'content' => '', 'color' => '#6366f1'];
$colors = ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
?>
<section class="page-header">
    <div>
        <h1 class="page-title"><?= e($heading) ?></h1>
        <p class="page-subtitle">Заполните заголовок, текст и цвет заметки.</p>
    </div>
    <a class="button button--secondary" href="/notes">Отмена</a>
</section>

<form class="form note-form" action="<?= e($action) ?>" method="post" x-data="{ color: '<?= e($note['color']) ?>' }" novalidate>
    <?= Csrf::field() ?>

    <label class="field">
        <span>Заголовок</span>
        <input type="text" name="title" value="<?= e($note['title']) ?>" maxlength="255" required>
        <?php if (isset($errors['title'])): ?>
            <small class="field-error"><?= e($errors['title']) ?></small>
        <?php endif; ?>
    </label>

    <label class="field">
        <span>Содержимое</span>
        <textarea name="content"><?= e($note['content']) ?></textarea>
    </label>

    <label class="field">
        <span>Цвет</span>
        <input type="hidden" name="color" :value="color">
        <span class="color-picker" role="radiogroup" aria-label="Цвет заметки">
            <?php foreach ($colors as $color): ?>
                <button
                    class="color-swatch"
                    :class="{ 'is-active': color === '<?= e($color) ?>' }"
                    type="button"
                    style="--swatch-color: <?= e($color) ?>"
                    aria-label="Выбрать цвет <?= e($color) ?>"
                    @click="color = '<?= e($color) ?>'"
                ></button>
            <?php endforeach; ?>
        </span>
        <?php if (isset($errors['color'])): ?>
            <small class="field-error"><?= e($errors['color']) ?></small>
        <?php endif; ?>
    </label>

    <div class="form-actions">
        <button class="button" type="submit">Сохранить</button>
        <a class="button button--secondary" href="/notes">Отмена</a>
    </div>
</form>

