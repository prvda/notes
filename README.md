# Notes

Тестовое задание: внутренний модуль заметок для сотрудников.

## Стек

- PHP 8.2
- PDO
- MySQL
- Alpine.js
- CSS

## Локальный запуск

1. Запустить MySQL в XAMPP Control Panel.
2. Скопировать `.env.example` в `.env`.
3. Проверить настройки БД в `.env`.
4. Выполнить миграции и seed:

```powershell
C:\xampp\php\php.exe tools\migrate.php --seed
```

5. Запустить PHP built-in server:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
```

6. Открыть `http://127.0.0.1:8000/login`.

Тестовые пользователи:

- email: `demo@example.com`
- password: `password`
- email: `second@example.com`
- password: `password`

## Что реализовано

- Минимальная авторизация через session login/logout.
- SQL migrations для `users` и `notes`.
- Seed двух demo-пользователей и отдельных заметок для проверки доступа.
- CRUD заметок текущего пользователя.
- Поиск по заголовку через `q`.
- Пагинация по 20 заметок.
- Закреплённые заметки сверху.
- Полный просмотр заметки по клику на карточку.
- AJAX pin/unpin через Alpine.js и `fetch`.
- Проверка `user_id` для edit/update/delete/toggle-pin.
- CSRF-токен во всех POST-запросах.
- PDO prepared statements для запросов к БД.
- Экранирование пользовательских данных при выводе.

## Маршруты

Все маршруты заметок требуют авторизацию.

| Метод | Путь | Действие |
| --- | --- | --- |
| GET | `/login` | Форма входа |
| POST | `/login` | Вход |
| POST | `/logout` | Выход |
| GET | `/notes` | Список заметок |
| GET | `/notes/create` | Форма создания |
| POST | `/notes` | Сохранение |
| GET | `/notes/{id}` | Полный просмотр |
| GET | `/notes/{id}/edit` | Форма редактирования |
| POST | `/notes/{id}` | Обновление |
| POST | `/notes/{id}/delete` | Удаление |
| POST | `/notes/{id}/toggle-pin` | Переключение закрепления |

## Проверка

Проверить синтаксис PHP:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
```

Основные ручные сценарии:

- гость перенаправляется на `/login`;
- вход и выход работают;
- заметки создаются, редактируются и удаляются;
- чужие заметки недоступны по `id`;
- `demo@example.com` и `second@example.com` видят только свои заметки;
- поиск фильтрует список по заголовку;
- пагинация работает по 20 записей;
- закрепление меняется без перезагрузки страницы;
- POST без CSRF-токена отклоняется;
- HTML в заголовке и тексте выводится безопасно.
