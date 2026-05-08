# Деплой на Railway.app

## Шаг 1: Создайте репозиторий на GitHub

1. Перейдите на https://github.com/new
2. Название репозитория: `laravel-visit-tracker` (или любое другое)
3. Выберите **Public** или **Private**
4. **НЕ** добавляйте README, .gitignore или лицензию (они уже есть)
5. Нажмите **Create repository**

---

## Шаг 2: Загрузите проект на GitHub

Откройте PowerShell в папке проекта и выполните:

```powershell
# Инициализируйте Git (если еще не сделали)
git init

# Добавьте все файлы
git add .

# Создайте коммит
git commit -m "Initial commit: Laravel Visit Tracker"

# Добавьте удаленный репозиторий (замените YOUR_USERNAME и YOUR_REPO)
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git

# Отправьте код на GitHub
git branch -M main
git push -u origin main
```

**Важно:** Замените `YOUR_USERNAME` и `YOUR_REPO` на ваши данные!

---

## Шаг 3: Разверните на Railway

### 3.1 Регистрация

1. Перейдите на https://railway.app
2. Нажмите **"Start a New Project"**
3. Войдите через **GitHub**

### 3.2 Создание проекта

1. Нажмите **"New Project"**
2. Выберите **"Deploy from GitHub repo"**
3. Выберите ваш репозиторий `laravel-visit-tracker`
4. Railway автоматически начнет деплой

### 3.3 Настройка переменных окружения

1. Откройте ваш проект в Railway
2. Перейдите на вкладку **"Variables"**
3. Добавьте следующие переменные:

```
APP_NAME=Visit Tracker
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
SESSION_DRIVER=file
CACHE_DRIVER=file
```

4. Для `APP_KEY` выполните локально:
```powershell
C:\php\php.exe artisan key:generate --show
```
Скопируйте результат (например: `base64:abc123...`) и добавьте как переменную `APP_KEY`

### 3.4 Получите URL приложения

1. В Railway перейдите на вкладку **"Settings"**
2. Найдите секцию **"Domains"**
3. Нажмите **"Generate Domain"**
4. Скопируйте URL (например: `https://laravel-visit-tracker-production.up.railway.app`)

### 3.5 Обновите APP_URL

1. Вернитесь на вкладку **"Variables"**
2. Измените `APP_URL` на ваш Railway URL
3. Нажмите **"Redeploy"**

---

## Шаг 4: Выполните миграции и создайте пользователя

После успешного деплоя:

1. В Railway откройте ваш проект
2. Перейдите на вкладку с вашим сервисом
3. Нажмите на три точки (⋯) → **"View Logs"**
4. Убедитесь, что миграции выполнились (в Procfile уже настроено)

Если нужно создать пользователя вручную:

1. Нажмите на три точки (⋯) → **"Shell"**
2. Выполните:
```bash
php artisan db:seed --class=UserSeeder
```

---

## Шаг 5: Проверьте работу

Откройте ваш Railway URL в браузере:

1. **Главная страница:** `https://your-app.railway.app`
2. **Вход:** `https://your-app.railway.app/login`
   - Email: `admin@example.com`
   - Пароль: `password`
3. **Dashboard:** `https://your-app.railway.app/dashboard`
4. **API шуток:** `https://your-app.railway.app/api/jokes`
5. **Демо полей:** `https://your-app.railway.app/demo.html`
6. **Демо трекера:** `https://your-app.railway.app/tracker-demo.html`

---

## Шаг 6: Загрузите первую шутку

В Railway Shell выполните:
```bash
php artisan app:fetch-jokes
```

Или откройте демо-страницу трекера — она автоматически создаст данные.

---

## 🎉 Готово!

Ваш проект развернут и доступен по ссылке Railway!

---

## 🔧 Полезные команды Railway

```bash
# Просмотр логов
railway logs

# Выполнение команд
railway run php artisan migrate
railway run php artisan db:seed

# Перезапуск
railway restart
```

---

## ⚠️ Важные замечания

1. **Бесплатный план:** $5 кредитов/месяц (обычно хватает на 500+ часов)
2. **SQLite:** База данных будет сброшена при каждом редеплое (для продакшена используйте PostgreSQL)
3. **Засыпание:** Приложение не засыпает (в отличие от Render)
4. **HTTPS:** Автоматически включен

---

## 📊 Мониторинг

В Railway Dashboard вы можете видеть:
- CPU и Memory usage
- Логи в реальном времени
- Метрики запросов
- Статус деплоя

---

## 🐛 Решение проблем

**Проблема:** Ошибка 500 после деплоя

**Решение:**
1. Проверьте логи в Railway
2. Убедитесь, что `APP_KEY` установлен
3. Проверьте, что миграции выполнились

**Проблема:** База данных пустая

**Решение:**
```bash
railway run php artisan migrate --force
railway run php artisan db:seed --class=UserSeeder
```

---

## 📝 Следующие шаги

После успешного деплоя:

1. Создайте тестовые данные через демо-страницу
2. Проверьте Dashboard с графиками
3. Поделитесь ссылкой для демонстрации!

---

**Готово! Теперь просто дайте мне ссылку на ваш GitHub репозиторий, и я помогу с настройкой Railway.**
