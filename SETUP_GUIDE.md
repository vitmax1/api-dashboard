# Инструкция по запуску и проверке проекта

## Предварительные требования

Убедитесь, что у вас установлено:
- ✅ PHP 8.5.1 (уже установлен в `C:\php\`)
- ✅ Composer
- ✅ SQLite3 (обычно идет с PHP)

---

## Шаг 1: Установка Composer

Если Composer не установлен, скачайте и установите:

```powershell
# Скачайте Composer
Invoke-WebRequest -Uri https://getcomposer.org/installer -OutFile composer-setup.php

# Установите Composer
C:\php\php.exe composer-setup.php --install-dir=C:\php --filename=composer.phar

# Удалите установщик
Remove-Item composer-setup.php
```

Теперь Composer доступен через:
```powershell
C:\php\php.exe C:\php\composer.phar
```

---

## Шаг 2: Установка зависимостей Laravel

```powershell
# Перейдите в директорию проекта
cd C:\_JOB\test-work-for-php-dev

# Установите зависимости
C:\php\php.exe C:\php\composer.phar install
```

**Примечание:** Если `composer.phar` не найден, используйте глобальную команду `composer` (если установлен глобально).

---

## Шаг 3: Настройка окружения

Создайте файл `.env`:

```powershell
# Скопируйте пример (если есть) или создайте новый
Copy-Item .env.example .env -ErrorAction SilentlyContinue
```

Если файла `.env.example` нет, создайте `.env` вручную со следующим содержимым:

```env
APP_NAME="Visit Tracker"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=C:\_JOB\test-work-for-php-dev\database\database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## Шаг 4: Генерация ключа приложения

```powershell
C:\php\php.exe artisan key:generate
```

Эта команда автоматически добавит `APP_KEY` в файл `.env`.

---

## Шаг 5: Создание базы данных SQLite

```powershell
# Создайте пустой файл базы данных
New-Item -ItemType File -Path database\database.sqlite -Force
```

---

## Шаг 6: Запуск миграций

```powershell
C:\php\php.exe artisan migrate
```

Вы должны увидеть:
```
Migration table created successfully.
Migrating: 2026_05_07_163237_create_jokes_table
Migrated:  2026_05_07_163237_create_jokes_table
Migrating: 2026_05_07_165050_create_visits_table
Migrated:  2026_05_07_165050_create_visits_table
Migrating: 2026_05_07_165430_create_users_table
Migrated:  2026_05_07_165430_create_users_table
```

---

## Шаг 7: Создание тестового пользователя

```powershell
C:\php\php.exe artisan db:seed --class=UserSeeder
```

Будет создан пользователь:
- **Email:** `admin@example.com`
- **Пароль:** `password`

---

## Шаг 8: Запуск сервера разработки

```powershell
C:\php\php.exe artisan serve
```

Вы должны увидеть:
```
Starting Laravel development server: http://127.0.0.1:8000
```

**Сервер запущен!** Оставьте это окно PowerShell открытым.

---

## Шаг 9: Проверка работы системы

### 9.1 Проверка API шуток (Этап 1)

Откройте браузер и перейдите:
```
http://localhost:8000/api/jokes
```

**Ожидаемый результат:** Пустой массив `[]` (шутки еще не загружены).

Запустите команду загрузки шуток:
```powershell
# Откройте новое окно PowerShell
C:\php\php.exe artisan app:fetch-jokes
```

Обновите страницу `http://localhost:8000/api/jokes` — должна появиться одна шутка в JSON формате.

---

### 9.2 Проверка динамической видимости полей (Этап 2)

Откройте в браузере:
```
http://localhost:8000/demo.html
```

**Проверка:**
1. При загрузке все поля скрыты
2. Выберите "Личные данные" — появятся поля с `personal` в name
3. Выберите "Компания" — появятся поля с `company` в name
4. Выберите "Доставка" — появятся поля с `delivery` в name

---

### 9.3 Проверка трекера посещений (Этап 3)

#### A. Проверка страницы входа

Откройте:
```
http://localhost:8000/login
```

**Проверка:**
- Красивая страница входа с градиентом
- Форма с полями Email и Пароль

Войдите с учетными данными:
- Email: `admin@example.com`
- Пароль: `password`

После входа вы будете перенаправлены на Dashboard.

---

#### B. Проверка Dashboard

После входа вы должны увидеть:
- Заголовок "📊 Статистика посещений"
- Кнопку "Выход"
- Три графика (пока пустые, т.к. нет данных)

---

#### C. Генерация тестовых данных

Откройте новое окно PowerShell и выполните:

```powershell
# Создайте несколько тестовых посещений
$headers = @{
    "Content-Type" = "application/json"
}

# Посещение 1 - Desktop из Москвы
$body1 = @{
    device = "Desktop"
    city = "Moscow"
    payload = @{
        userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
        language = "ru-RU"
    }
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/track" -Method POST -Headers $headers -Body $body1

# Посещение 2 - Mobile из Санкт-Петербурга
$body2 = @{
    device = "Mobile"
    city = "Saint Petersburg"
    payload = @{
        userAgent = "Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)"
        language = "ru-RU"
    }
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/track" -Method POST -Headers $headers -Body $body2

# Посещение 3 - Desktop из Москвы
$body3 = @{
    device = "Desktop"
    city = "Moscow"
    payload = @{
        userAgent = "Mozilla/5.0 (Windows NT 10.0)"
        language = "en-US"
    }
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/track" -Method POST -Headers $headers -Body $body3

# Посещение 4 - Mobile из Казани
$body4 = @{
    device = "Mobile"
    city = "Kazan"
    payload = @{
        userAgent = "Mozilla/5.0 (Android 11)"
        language = "ru-RU"
    }
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/track" -Method POST -Headers $headers -Body $body4

Write-Host "✅ Создано 4 тестовых посещения" -ForegroundColor Green
```

---

#### D. Проверка графиков

Обновите страницу Dashboard (`http://localhost:8000/dashboard`).

**Вы должны увидеть:**

1. **График по часам:**
   - Линия с точками, показывающая посещения в текущем часе

2. **Круговая диаграмма по городам:**
   - Moscow (2 посещения)
   - Saint Petersburg (1 посещение)
   - Kazan (1 посещение)

3. **Диаграмма по устройствам:**
   - Desktop (2 посещения)
   - Mobile (2 посещения)

---

#### E. Проверка реального трекера

Откройте демо-страницу с трекером:
```
http://localhost:8000/tracker-demo.html
```

**Проверка:**
1. Страница загружается
2. Через 2 секунды статус меняется на "✅ Трекер активен"
3. Откройте консоль браузера (F12) — должно быть сообщение `[Tracker] Visit recorded`

Обновите Dashboard — должно появиться новое посещение с вашим реальным городом (определенным по IP).

---

## Шаг 10: Запуск тестов

```powershell
# Запустите все тесты
C:\php\php.exe artisan test
```

**Ожидаемый результат:**
```
PASS  Tests\Feature\FetchJokesCommandTest
✓ fetch jokes command stores joke in database
✓ fetch jokes command does not duplicate existing joke

PASS  Tests\Feature\JokeApiTest
✓ jokes endpoint returns all jokes as json
✓ jokes endpoint returns empty array when no jokes

PASS  Tests\Feature\VisitTrackingTest
✓ track endpoint stores visit data
✓ track endpoint validates device type
✓ track endpoint requires device field
✓ track endpoint accepts mobile device
✓ track endpoint stores ip address
✓ track endpoint accepts null city

PASS  Tests\Feature\DashboardAccessTest
✓ dashboard redirects unauthenticated users to login
✓ authenticated users can access dashboard
✓ login page is accessible
✓ user can login with valid credentials
✓ user cannot login with invalid credentials
✓ user can logout
✓ stats api requires authentication
✓ authenticated users can access stats api

Tests:    18 passed
Duration: 2.34s
```

---

## Шаг 11: Проверка планировщика (Этап 1)

Планировщик Laravel запускает команду `app:fetch-jokes` каждые 5 минут.

Для проверки запустите планировщик вручную:

```powershell
C:\php\php.exe artisan schedule:work
```

Эта команда будет работать в фоне и каждые 5 минут загружать новую шутку.

**Проверка:**
- Подождите 5 минут
- Откройте `http://localhost:8000/api/jokes`
- Должна появиться новая шутка

Для остановки нажмите `Ctrl+C`.

---

## Шаг 12: Проверка защиты маршрутов

### A. Попытка доступа без авторизации

Откройте браузер в режиме инкогнито и перейдите:
```
http://localhost:8000/dashboard
```

**Ожидаемый результат:** Редирект на `/login`

### B. Попытка доступа к API статистики без авторизации

В PowerShell выполните:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/stats" -Method GET
```

**Ожидаемый результат:** Ошибка 401 Unauthorized

---

## Шаг 13: Проверка встраивания трекера на внешний сайт

Создайте тестовый HTML файл где угодно (например, на рабочем столе):

**test-external.html:**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Внешний сайт</title>
</head>
<body>
    <h1>Это внешний сайт</h1>
    <p>Трекер встроен и работает.</p>

    <!-- Встраиваем трекер -->
    <script
        src="http://localhost:8000/tracker.js"
        data-api-url="http://localhost:8000/api/track"
    ></script>
</body>
</html>
```

Откройте этот файл в браузере (двойной клик).

**Проверка:**
1. Откройте консоль браузера (F12)
2. Должно быть сообщение `[Tracker] Visit recorded`
3. Обновите Dashboard — появится новое посещение

---

## Возможные проблемы и решения

### Проблема 1: "Class 'Illuminate\Foundation\Application' not found"

**Решение:** Установите зависимости Laravel:
```powershell
C:\php\php.exe C:\php\composer.phar install
```

### Проблема 2: "SQLSTATE[HY000]: unable to open database file"

**Решение:** Проверьте путь к БД в `.env`:
```env
DB_DATABASE=C:\_JOB\test-work-for-php-dev\database\database.sqlite
```

Убедитесь, что файл существует:
```powershell
Test-Path database\database.sqlite
```

### Проблема 3: "Target class [App\Http\Controllers\...] does not exist"

**Решение:** Убедитесь, что все контроллеры созданы. Проверьте:
```powershell
Get-ChildItem app\Http\Controllers\
```

### Проблема 4: CORS ошибка при загрузке трекера

**Решение:** Убедитесь, что сервер Laravel запущен и доступен по `http://localhost:8000`.

### Проблема 5: Графики не отображаются

**Решение:** 
1. Откройте консоль браузера (F12)
2. Проверьте наличие ошибок
3. Убедитесь, что Chart.js загружается (проверьте вкладку Network)

---

## Краткая шпаргалка команд

```powershell
# Запуск сервера
C:\php\php.exe artisan serve

# Загрузка шутки
C:\php\php.exe artisan app:fetch-jokes

# Запуск планировщика
C:\php\php.exe artisan schedule:work

# Запуск тестов
C:\php\php.exe artisan test

# Запуск миграций
C:\php\php.exe artisan migrate

# Создание пользователя
C:\php\php.exe artisan db:seed --class=UserSeeder

# Очистка кэша
C:\php\php.exe artisan cache:clear
C:\php\php.exe artisan config:clear
C:\php\php.exe artisan route:clear
```

---

## Итоговая проверка

✅ **Этап 1 (Шутки):**
- [ ] API `/api/jokes` возвращает данные
- [ ] Команда `app:fetch-jokes` работает
- [ ] Тесты проходят

✅ **Этап 2 (Динамические поля):**
- [ ] Страница `/demo.html` открывается
- [ ] Поля показываются/скрываются при выборе типа
- [ ] Скрипт работает в консоли браузера

✅ **Этап 3 (Трекер):**
- [ ] Страница `/login` работает
- [ ] Вход с учетными данными успешен
- [ ] Dashboard отображает графики
- [ ] API `/api/track` принимает данные
- [ ] Трекер работает на внешних страницах
- [ ] Все тесты проходят

---

## Доступ к проекту

После запуска сервера доступны следующие URL:

- **Главная:** http://localhost:8000/
- **Вход:** http://localhost:8000/login
- **Dashboard:** http://localhost:8000/dashboard
- **API шуток:** http://localhost:8000/api/jokes
- **API трекера:** http://localhost:8000/api/track
- **Демо полей:** http://localhost:8000/demo.html
- **Демо трекера:** http://localhost:8000/tracker-demo.html

**Учетные данные:**
- Email: `admin@example.com`
- Пароль: `password`

---

Готово! Проект полностью настроен и готов к использованию.
