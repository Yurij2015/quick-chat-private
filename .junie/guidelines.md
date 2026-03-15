# Artisan Usage Guidelines

Всі команди `php artisan` ПОВИННІ виконуватися всередині Docker-контейнера через Laravel Sail.

## Правило
Замість:
`php artisan <command>`

Використовуйте:
`./vendor/bin/sail artisan <command>`

Це забезпечує консистентність середовища розробки та правильну взаємодію з базою даних та іншими сервісами (Redis, Reverb).
