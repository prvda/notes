<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\NoteController;

$router->get('/', [AuthController::class, 'home']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/notes', [NoteController::class, 'index']);
$router->get('/notes/create', [NoteController::class, 'create']);
$router->post('/notes', [NoteController::class, 'store']);
$router->get('/notes/{id}', [NoteController::class, 'show']);
$router->get('/notes/{id}/edit', [NoteController::class, 'edit']);
$router->post('/notes/{id}', [NoteController::class, 'update']);
$router->post('/notes/{id}/delete', [NoteController::class, 'destroy']);
$router->post('/notes/{id}/toggle-pin', [NoteController::class, 'togglePin']);

