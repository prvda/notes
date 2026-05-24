<?php

use App\Support\Csrf;

$errors = $errors ?? [];
?>
<section class="auth-panel">
    <div class="auth-panel__header">
        <h1 class="page-title">Вход</h1>
    </div>

    <form class="form auth-form" action="/login" method="post" novalidate>
        <?= Csrf::field() ?>

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email" required>
            <?php if (isset($errors['email'])): ?>
                <small class="field-error"><?= e($errors['email']) ?></small>
            <?php endif; ?>
        </label>

        <label class="field">
            <span>Пароль</span>
            <input type="password" name="password" autocomplete="current-password" required>
            <?php if (isset($errors['password'])): ?>
                <small class="field-error"><?= e($errors['password']) ?></small>
            <?php endif; ?>
        </label>

        <button class="button" type="submit">Войти</button>
    </form>
</section>

