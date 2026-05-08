# Система статистики посещений

## Обзор

Полнофункциональная система отслеживания посещений веб-сайтов, состоящая из:
- **Клиентский JS-трекер** — легковесный скрипт для встраивания на любой сайт
- **Backend API** — Laravel приложение для сбора и хранения данных
- **Dashboard** — защищенная панель управления с визуализацией данных

---

## Архитектура системы

### 1. Клиентский компонент (JS-трекер)

**Файл:** `public/tracker.js`

#### Как работает определение устройства:

```javascript
function detectDevice() {
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;
    const mobileRegex = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet/i;
    return mobileRegex.test(userAgent.toLowerCase()) ? 'Mobile' : 'Desktop';
}
```

**Логика:**
- Проверяет строку User Agent браузера
- Использует регулярное выражение для поиска ключевых слов мобильных устройств
- Возвращает `'Mobile'` или `'Desktop'`

**Поддерживаемые устройства:**
- Android (смартфоны и планшеты)
- iOS (iPhone, iPad, iPod)
- Windows Mobile
- BlackBerry
- Opera Mini
- Другие мобильные браузеры

#### Как работает определение города:

```javascript
async function getCity() {
    const response = await fetch('http://ip-api.com/json/?fields=city', {
        method: 'GET',
        cache: 'no-cache',
    });
    const data = await response.json();
    return data.city || null;
}
```

**Используемое API:** [ip-api.com](http://ip-api.com/)

**Характеристики:**
- Бесплатное использование: до 45 запросов в минуту
- Не требует API ключа
- Возвращает город на основе IP-адреса пользователя
- Поддерживает геолокацию по всему миру

**Альтернативные API (если нужна замена):**
- `ipapi.co` — 1000 запросов/день бесплатно
- `ipinfo.io` — 50000 запросов/месяц бесплатно
- `geojs.io` — без лимитов, но менее точное

#### Собираемые данные:

**Основные поля:**
- `device` — тип устройства (Desktop/Mobile)
- `city` — город пользователя
- `ip` — IP-адрес (определяется на сервере)

**Дополнительные данные (payload):**
- `userAgent` — полная строка User Agent
- `language` — язык браузера (например, `ru-RU`)
- `screenResolution` — разрешение экрана (например, `1920x1080`)
- `referrer` — источник перехода или `'direct'`
- `timestamp` — время посещения в ISO формате

#### Асинхронная отправка:

```javascript
async function sendVisitData(apiUrl, data) {
    await fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
        mode: 'cors',
    });
}
```

**Преимущества:**
- Не блокирует загрузку основного сайта
- Использует `async/await` для чистого кода
- Обрабатывает ошибки без прерывания работы сайта
- Поддерживает CORS для кросс-доменных запросов

---

### 2. Backend (Laravel + SQLite)

#### База данных

**Таблица `visits`:**

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | INTEGER | Первичный ключ |
| `ip` | VARCHAR(45) | IP-адрес посетителя (поддержка IPv6) |
| `city` | VARCHAR(255) | Город (nullable) |
| `device` | VARCHAR(20) | Desktop или Mobile |
| `payload` | JSON | Дополнительные данные |
| `created_at` | TIMESTAMP | Время посещения |

**Индексы:**
- `created_at` — для быстрой выборки по времени
- `city` — для группировки по городам
- `device` — для статистики по устройствам

**Почему SQLite:**
- Простота развертывания (не требует отдельного сервера БД)
- Достаточная производительность для малых и средних проектов
- Встроенная поддержка JSON
- Легкое резервное копирование (один файл)

#### API Endpoints

**1. POST `/api/track` — Запись посещения**

Публичный endpoint без авторизации.

**Запрос:**
```json
{
  "device": "Desktop",
  "city": "Moscow",
  "payload": {
    "userAgent": "Mozilla/5.0...",
    "language": "ru-RU",
    "screenResolution": "1920x1080",
    "referrer": "https://google.com",
    "timestamp": "2026-05-07T16:30:00.000Z"
  }
}
```

**Валидация:**
- `device` — обязательное, строка, только `Desktop` или `Mobile`
- `city` — опциональное, строка, максимум 255 символов
- `payload` — опциональное, объект JSON

**Ответ (успех):**
```json
{
  "success": true,
  "message": "Visit recorded successfully"
}
```

**Ответ (ошибка валидации):**
```json
{
  "success": false,
  "errors": {
    "device": ["The device field must be Desktop or Mobile."]
  }
}
```

**2. GET `/api/dashboard/stats` — Получение статистики**

Требует авторизации (middleware `auth:sanctum`).

**Ответ:**
```json
{
  "hourly": [
    {"hour": "2026-05-07 14:00:00", "unique_visits": 5},
    {"hour": "2026-05-07 15:00:00", "unique_visits": 8}
  ],
  "cities": [
    {"city": "Moscow", "count": 120},
    {"city": "Saint Petersburg", "count": 85}
  ],
  "devices": [
    {"device": "Desktop", "count": 150},
    {"device": "Mobile", "count": 55}
  ]
}
```

#### Авторизация

**Система:** Laravel встроенная аутентификация

**Компоненты:**
- `AuthController` — обработка входа/выхода
- Middleware `auth` — защита веб-маршрутов
- Middleware `auth:sanctum` — защита API маршрутов
- Session-based аутентификация для веб-интерфейса

**Защищенные маршруты:**
- `/dashboard` — страница статистики
- `/api/dashboard/stats` — API статистики

**Публичные маршруты:**
- `/login` — страница входа
- `/api/track` — прием данных от трекера

**Тестовый пользователь:**
- Email: `admin@example.com`
- Пароль: `password`

---

### 3. Визуализация (Dashboard)

**Файл:** `resources/views/dashboard/index.blade.php`

#### Используемая библиотека: Chart.js 4.4.0

**Почему Chart.js:**

✅ **Преимущества:**
1. **Легковесность** — ~60KB минифицированная, без зависимостей
2. **Простота использования** — декларативный API, минимум кода
3. **Богатая функциональность** — 8 типов графиков из коробки
4. **Отзывчивость** — автоматическая адаптация под размер экрана
5. **Активная поддержка** — регулярные обновления, большое сообщество
6. **Бесплатная** — MIT лицензия
7. **Хорошая документация** — примеры и туториалы

**Альтернативы и сравнение:**

| Библиотека | Размер | Сложность | Интерактивность | Лицензия |
|------------|--------|-----------|-----------------|----------|
| **Chart.js** | 60KB | Низкая | Средняя | MIT (бесплатно) |
| D3.js | 250KB | Высокая | Высокая | BSD (бесплатно) |
| Highcharts | 150KB | Средняя | Высокая | Платная для коммерции |
| ApexCharts | 140KB | Средняя | Высокая | MIT (бесплатно) |
| Plotly.js | 3MB | Средняя | Очень высокая | MIT (бесплатно) |

**Вывод:** Chart.js оптимален для данной задачи — простые графики без избыточной сложности.

#### Реализованные графики

**1. Line Chart — Уникальные посещения по часам**

```javascript
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['14:00', '15:00', '16:00'],
        datasets: [{
            label: 'Уникальные посещения',
            data: [5, 8, 12],
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            tension: 0.4,
            fill: true,
        }]
    }
});
```

**Особенности:**
- Показывает тренд посещений за последние 24 часа
- Группировка по часам
- Подсчет уникальных IP-адресов (не дублирование)
- Плавная кривая (`tension: 0.4`)
- Заливка под графиком для лучшей читаемости

**2. Pie Chart — Распределение по городам**

```javascript
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Moscow', 'Saint Petersburg', 'Kazan'],
        datasets: [{
            data: [120, 85, 45],
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
        }]
    }
});
```

**Особенности:**
- Топ-10 городов по количеству посещений
- Разноцветные сегменты для различения
- Легенда справа от графика
- Процентное соотношение при наведении

**3. Doughnut Chart — Распределение по устройствам**

```javascript
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Desktop', 'Mobile'],
        datasets: [{
            data: [150, 55],
            backgroundColor: ['#36A2EB', '#FF6384'],
        }]
    }
});
```

**Особенности:**
- Простое сравнение Desktop vs Mobile
- Компактное отображение
- Легенда снизу

#### Автообновление

```javascript
setInterval(loadStats, 30000); // Обновление каждые 30 секунд
```

Графики автоматически обновляются без перезагрузки страницы.

---

## Установка и настройка

### Требования

- PHP 8.1+
- Composer
- SQLite3
- Node.js (опционально, для сборки фронтенда)

### Шаги установки

**1. Установка зависимостей:**

```bash
composer install
```

**2. Настройка окружения:**

Создайте файл `.env`:

```env
APP_NAME="Visit Tracker"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

**3. Генерация ключа приложения:**

```bash
php artisan key:generate
```

**4. Создание базы данных:**

```bash
touch database/database.sqlite
```

**5. Запуск миграций:**

```bash
php artisan migrate
```

**6. Создание тестового пользователя:**

```bash
php artisan db:seed --class=UserSeeder
```

**7. Запуск сервера:**

```bash
php artisan serve
```

Приложение доступно по адресу: `http://localhost:8000`

---

## Использование

### Встраивание трекера на сайт

Добавьте следующий код перед закрывающим тегом `</body>`:

```html
<script 
    src="https://your-domain.com/tracker.js" 
    data-api-url="https://your-domain.com/api/track"
></script>
```

**Параметры:**
- `src` — URL вашего трекера
- `data-api-url` — URL API endpoint для приема данных

### Вход в панель управления

1. Откройте `http://your-domain.com/login`
2. Введите учетные данные:
   - Email: `admin@example.com`
   - Пароль: `password`
3. Вы будете перенаправлены на `/dashboard`

### Просмотр статистики

Dashboard обновляется автоматически каждые 30 секунд и показывает:
- График посещений по часам (последние 24 часа)
- Топ-10 городов
- Соотношение Desktop/Mobile

---

## Тестирование

### Запуск всех тестов:

```bash
php artisan test
```

### Запуск конкретного теста:

```bash
php artisan test --filter=VisitTrackingTest
php artisan test --filter=DashboardAccessTest
```

### Покрытие тестами:

**VisitTrackingTest:**
- ✅ Запись данных в БД при обращении к API
- ✅ Валидация типа устройства
- ✅ Обязательность поля device
- ✅ Поддержка Mobile и Desktop
- ✅ Сохранение IP-адреса
- ✅ Поддержка null значения для city

**DashboardAccessTest:**
- ✅ Редирект неавторизованных на /login
- ✅ Доступ авторизованных к dashboard
- ✅ Доступность страницы входа
- ✅ Вход с валидными данными
- ✅ Отказ при невалидных данных
- ✅ Выход из системы
- ✅ Защита API статистики
- ✅ Доступ авторизованных к API статистики

---

## Безопасность

### Реализованные меры:

1. **Авторизация:**
   - Session-based аутентификация
   - CSRF защита для форм
   - Middleware защита маршрутов

2. **Валидация данных:**
   - Строгая валидация входящих данных
   - Ограничение типов устройств
   - Санитизация JSON payload

3. **Защита от инъекций:**
   - Использование Eloquent ORM (защита от SQL injection)
   - Prepared statements
   - Экранирование в Blade шаблонах

4. **Rate Limiting:**
   - Можно добавить через middleware `throttle`
   - Пример: `Route::middleware('throttle:60,1')` — 60 запросов в минуту

### Рекомендации для продакшена:

1. Измените пароль администратора
2. Настройте HTTPS
3. Добавьте rate limiting на `/api/track`
4. Настройте CORS политику
5. Включите логирование ошибок
6. Настройте резервное копирование БД

---

## Масштабирование

### Для высоких нагрузок:

1. **Переход на PostgreSQL/MySQL:**
   - Лучшая производительность при >100K записей
   - Поддержка репликации
   - Более эффективные индексы

2. **Кэширование:**
   - Redis для кэширования статистики
   - Обновление кэша по расписанию

3. **Очередь задач:**
   - Асинхронная обработка посещений через Laravel Queue
   - Снижение нагрузки на API

4. **CDN для трекера:**
   - Размещение `tracker.js` на CDN
   - Уменьшение задержек для пользователей

---

## Структура файлов проекта

```
C:\_JOB\test-work-for-php-dev/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── FetchJokes.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── JokeController.php
│   │       └── VisitController.php
│   └── Models/
│       ├── Joke.php
│       ├── User.php
│       └── Visit.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2026_05_07_163237_create_jokes_table.php
│   │   ├── 2026_05_07_165050_create_visits_table.php
│   │   └── 2026_05_07_165430_create_users_table.php
│   └── seeders/
│       └── UserSeeder.php
├── public/
│   ├── js/
│   │   └── field-visibility.js
│   ├── tracker.js
│   └── demo.html
├── resources/
│   └── views/
│       ├── auth/
│       │   └── login.blade.php
│       └── dashboard/
│           └── index.blade.php
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
├── tests/
│   └── Feature/
│       ├── DashboardAccessTest.php
│       ├── FetchJokesCommandTest.php
│       ├── JokeApiTest.php
│       └── VisitTrackingTest.php
├── FIELD_VISIBILITY_ANALYSIS.md
├── README.md
└── VISIT_TRACKER_DOCUMENTATION.md (этот файл)
```

---

## FAQ

**Q: Можно ли использовать трекер на нескольких сайтах?**  
A: Да, просто укажите один и тот же `data-api-url` на всех сайтах.

**Q: Как отличить посещения разных сайтов?**  
A: Добавьте в payload поле `site` с идентификатором сайта:

```javascript
const payload = {
    site: 'example.com',
    // ... остальные данные
};
```

**Q: Трекер работает с одностраничными приложениями (SPA)?**  
A: Да, но нужно вызывать `initTracker()` при каждой смене маршрута.

**Q: Как удалить старые данные?**  
A: Создайте команду для очистки:

```php
Visit::where('created_at', '<', now()->subDays(30))->delete();
```

**Q: Можно ли экспортировать данные?**  
A: Да, добавьте endpoint для экспорта в CSV/Excel или используйте прямой доступ к SQLite.

---

## Поддержка

Для вопросов и предложений создайте issue в репозитории проекта.
