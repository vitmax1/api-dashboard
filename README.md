# Visit Tracker - Система отслеживания посещений

Веб-приложение на Laravel для отслеживания и визуализации статистики посещений с динамическими полями и аналитическим дашбордом.

## 🌐 Демо

**URL:** https://api-dashboard-btpd.onrender.com

**Данные для входа:**
- Email: `admin@example.com`
- Password: `password`

> ⚠️ **Важно:** Приложение размещено на бесплатном плане Render.com. После 15 минут неактивности сервис засыпает. Первая загрузка после сна может занять ~30 секунд. Дашборд автоматически повторит попытку загрузки данных.

## 📊 Возможности

- **Аутентификация** - защищённый доступ к дашборду
- **Визуализация данных** - интерактивные графики с Chart.js
- **Статистика в реальном времени:**
  - Уникальные посещения по часам (последние 24 часа)
  - Распределение по городам (топ-10)
  - Распределение по устройствам (mobile/desktop)
- **Автообновление** - данные обновляются каждые 30 секунд
- **Адаптивный дизайн** - работает на всех устройствах

## 🚀 Как использовать

### 1. Просмотр дашборда

1. Откройте https://api-dashboard-btpd.onrender.com
2. Войдите используя данные выше
3. Дашборд автоматически загрузит статистику
4. Графики обновляются каждые 30 секунд

### 2. Добавление новых записей в базу

#### Вариант A: Через API endpoint

Создайте API маршрут для добавления визитов. Добавьте в `routes/api.php`:

```php
use App\Models\Visit;
use Illuminate\Http\Request;

Route::post('/visits', function (Request $request) {
    $visit = Visit::create([
        'ip' => $request->ip(),
        'city' => $request->input('city'),
        'device' => $request->input('device', 'desktop'),
        'payload' => $request->input('payload', []),
        'created_at' => now()
    ]);
    
    return response()->json($visit, 201);
});
```

Затем отправьте POST запрос:

```bash
curl -X POST https://api-dashboard-btpd.onrender.com/api/visits \
  -H "Content-Type: application/json" \
  -d '{
    "city": "Москва",
    "device": "mobile",
    "payload": {
      "user_agent": "Mozilla/5.0...",
      "referer": "https://google.com"
    }
  }'
```

#### Вариант B: Через Tinker (локально)

Если у вас есть локальная копия проекта:

```bash
php artisan tinker
```

Затем выполните:

```php
use App\Models\Visit;

Visit::create([
    'ip' => '192.168.1.100',
    'city' => 'Санкт-Петербург',
    'device' => 'desktop',
    'payload' => [
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'referer' => 'https://yandex.ru'
    ],
    'created_at' => now()
]);
```

Для массового добавления:

```php
use App\Models\Visit;

$cities = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург'];
$devices = ['mobile', 'desktop'];

for ($i = 0; $i < 50; $i++) {
    Visit::create([
        'ip' => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255),
        'city' => $cities[array_rand($cities)],
        'device' => $devices[array_rand($devices)],
        'payload' => ['user_agent' => 'Test Agent'],
        'created_at' => now()->subHours(rand(0, 23))
    ]);
}
```

#### Вариант C: Через сидер (автоматически)

Сидер `VisitSeeder` автоматически запускается при деплое и добавляет 100 тестовых визитов за последние 24 часа.

Для ручного запуска:

```bash
php artisan db:seed --class=VisitSeeder
```

### 3. Структура данных Visit

Таблица `visits` содержит следующие поля:

| Поле | Тип | Описание | Обязательное |
|------|-----|----------|--------------|
| `id` | bigint | Уникальный идентификатор | Да |
| `ip` | string(45) | IP-адрес посетителя | Да |
| `city` | string | Город | Нет |
| `device` | string(20) | Тип устройства (mobile/desktop) | Да |
| `payload` | json | Дополнительные данные | Нет |
| `created_at` | timestamp | Время визита | Да |

**Пример payload:**
```json
{
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "referer": "https://google.com",
  "screen_resolution": "1920x1080",
  "language": "ru-RU"
}
```

## 🛠 Технологии

- **Backend:** PHP 8.2, Laravel 11
- **Database:** SQLite
- **Frontend:** Vanilla JS, Chart.js 4.4
- **Deployment:** Docker, Render.com
- **Web Server:** Apache 2.4

## 📁 Структура проекта

```
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php      # Аутентификация
│   │   └── DashboardController.php # Статистика
│   └── Models/
│       ├── User.php                # Модель пользователя
│       └── Visit.php               # Модель визита
├── database/
│   ├── migrations/
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   └── 2026_05_07_165050_create_visits_table.php
│   └── seeders/
│       ├── UserSeeder.php          # Создание админа
│       └── VisitSeeder.php         # Генерация тестовых визитов
├── resources/views/
│   ├── auth/login.blade.php        # Форма входа
│   └── dashboard/index.blade.php   # Дашборд с графиками
├── routes/
│   └── web.php                     # Маршруты приложения
├── Dockerfile                      # Docker конфигурация
├── render.yaml                     # Render.com конфигурация
└── .dockerignore                   # Исключения для Docker
```

## 🔧 Локальная разработка

### Требования

- PHP 8.2+
- Composer
- SQLite extension

### Установка

```bash
# Клонировать репозиторий
git clone https://github.com/vitmax1/api-dashboard.git
cd api-dashboard

# Установить зависимости
composer install

# Создать .env файл
cp .env.example .env

# Сгенерировать ключ приложения
php artisan key:generate

# Создать директорию для базы данных
mkdir -p storage/database

# Создать базу данных
touch storage/database/database.sqlite

# Запустить миграции
php artisan migrate

# Заполнить базу тестовыми данными
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=VisitSeeder

# Запустить сервер
php artisan serve
```

Приложение будет доступно по адресу: http://localhost:8000

## 📝 API Endpoints

| Метод | URL | Описание | Авторизация |
|-------|-----|----------|-------------|
| GET | `/` | Редирект на дашборд | Нет |
| GET | `/login` | Форма входа | Нет |
| POST | `/login` | Аутентификация | Нет |
| POST | `/logout` | Выход | Да |
| GET | `/dashboard` | Дашборд | Да |
| GET | `/api/dashboard/stats` | JSON со статистикой | Да |

### Пример ответа `/api/dashboard/stats`:

```json
{
  "hourly": [
    {
      "hour": "2026-05-09 10:00:00",
      "unique_visits": 5
    }
  ],
  "cities": [
    {
      "city": "Москва",
      "count": 25
    }
  ],
  "devices": [
    {
      "device": "mobile",
      "count": 60
    },
    {
      "device": "desktop",
      "count": 40
    }
  ]
}
```

## 🐳 Docker

Проект включает Dockerfile для деплоя на Render.com или любой другой платформе:

```bash
# Собрать образ
docker build -t visit-tracker .

# Запустить контейнер
docker run -p 80:80 \
  -e APP_KEY=base64:your-key-here \
  -e APP_ENV=production \
  visit-tracker
```

## 🐛 Известные особенности

1. **Холодный старт Render** - первая загрузка после сна занимает ~30 секунд
2. **Тестовые данные** - при каждом перезапуске контейнера генерируются новые тестовые визиты (100 записей)
3. **SQLite** - база данных не персистентна между деплоями на бесплатном плане Render
4. **Автообновление** - дашборд автоматически обновляет данные каждые 30 секунд

## 🔐 Безопасность

- Все маршруты дашборда защищены middleware `auth`
- CSRF защита на всех формах
- Пароли хешируются через bcrypt
- Рекомендуется сменить пароль админа после первого входа

## 📧 Контакты

- GitHub: [@vitmax1](https://github.com/vitmax1)
- Repository: https://github.com/vitmax1/api-dashboard

---

## 📋 Техническое задание (выполнено)

### Этап 3: Система статистики ✅

**Компонент 1 (Клиентский JS):** ✅
- Сбор IP-адреса, города и типа устройства
- Отправка данных на бэкенд через асинхронный запрос

**Компонент 2 (Backend & Dashboard):** ✅
- **Хранение:** SQLite база данных
- **Визуализация:** 
  - График посещений по часам (Line Chart) ✅
  - Круговая диаграмма по городам (Pie Chart) ✅
  - Дополнительно: Doughnut Chart по устройствам ✅
- **Доступ:** Защита авторизацией ✅

**Дата создания:** Май 2026  
**Версия:** 1.0.0
