<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\UserRepository;
use App\Support\Auth;
use App\Support\Csrf;

final class AuthController
{
    public function home(): void
    {
        redirect('/notes');
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/notes');
        }

        $errors = errors();
        View::render('auth/login', [
            'title' => 'Вход',
            'errors' => $errors,
        ]);
        clear_old_input();
    }

    public function login(): void
    {
        Csrf::requireValid();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $fieldErrors = [];

        if ($email === '') {
            $fieldErrors['email'] = 'Укажите email.';
        }

        if ($password === '') {
            $fieldErrors['password'] = 'Укажите пароль.';
        }

        if ($fieldErrors !== []) {
            back_with_errors($fieldErrors, ['email' => $email], '/login');
        }

        $user = (new UserRepository())->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            back_with_errors(['email' => 'Неверный email или пароль.'], ['email' => $email], '/login');
        }

        Auth::login($user);
        clear_old_input();
        redirect('/notes');
    }

    public function logout(): void
    {
        Csrf::requireValid();
        Auth::logout();
        redirect('/login');
    }
}

