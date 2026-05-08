# Динамическое управление видимостью полей формы

## Реализованное решение (Vanilla JS)

### Основной подход
Скрипт использует поиск по атрибуту `name` с методом `includes()` для определения видимости полей.

**Алгоритм:**
1. Находим select элемент по селектору
2. При изменении значения select получаем выбранное значение
3. Перебираем все input поля с атрибутом name
4. Если name содержит выбранное значение — показываем поле, иначе скрываем
5. Применяем логику при загрузке страницы к текущему значению

---

## Сравнение альтернативных подходов

### Подход 1: Data-атрибуты (рекомендуемый для масштабирования)

```javascript
// HTML разметка:
// <input type="text" name="user_name" data-visible-for="personal">
// <input type="text" name="company_name" data-visible-for="company">

function updateFieldVisibility() {
    const selectedValue = selectElement.value;
    const inputFields = document.querySelectorAll('input[data-visible-for]');

    inputFields.forEach(input => {
        const visibleFor = input.getAttribute('data-visible-for');
        input.style.display = visibleFor === selectedValue ? '' : 'none';
    });
}
```

**Преимущества:**
- Явная связь между полем и типом (не зависит от соглашений об именовании)
- Одно поле может быть видимо для нескольких типов: `data-visible-for="personal,company"`
- Легче поддерживать при рефакторинге (изменение name не ломает логику)

**Недостатки:**
- Требует модификации HTML (добавление data-атрибутов)
- Дублирование информации (name уже содержит тип)

---

### Подход 2: Делегирование событий + CSS классы

```javascript
// HTML разметка:
// <input type="text" name="personal_name" class="field-personal">
// <input type="text" name="company_name" class="field-company">

function updateFieldVisibility() {
    const selectedValue = selectElement.value;
    
    // Скрываем все поля
    document.querySelectorAll('[class*="field-"]').forEach(el => {
        el.style.display = 'none';
    });
    
    // Показываем нужные
    if (selectedValue) {
        document.querySelectorAll(`.field-${selectedValue}`).forEach(el => {
            el.style.display = '';
        });
    }
}
```

**Преимущества:**
- Более производительный (меньше итераций)
- Можно использовать CSS для анимаций переходов
- Легко добавить группировку полей

**Недостатки:**
- Требует добавления CSS классов в HTML
- Менее гибкий при динамическом добавлении полей

---

### Подход 3: Реактивная библиотека (Alpine.js)

```html
<div x-data="{ type: '' }">
    <select x-model="type">
        <option value="personal">Личные данные</option>
        <option value="company">Компания</option>
    </select>

    <input type="text" name="personal_name" x-show="type === 'personal'">
    <input type="text" name="company_name" x-show="type === 'company'">
</div>
```

**Преимущества:**
- Декларативный подход (логика в HTML)
- Автоматическая реактивность
- Встроенные анимации переходов
- Минимум JavaScript кода

**Недостатки:**
- Внешняя зависимость (~15KB gzipped)
- Избыточно для простой задачи
- Требует изучения синтаксиса Alpine.js

---

## Обоснование выбранного подхода

**Почему выбран поиск по атрибуту `name`:**

1. **Нулевая модификация HTML** — работает с существующей разметкой без добавления data-атрибутов или классов
2. **Соответствие соглашениям** — если в проекте уже используется префиксное именование полей (personal_*, company_*), логика естественно встраивается
3. **Универсальность** — скрипт можно подключить к любой форме без изменения HTML
4. **Простота** — понятная логика без дополнительных абстракций
5. **Легковесность** — нет внешних зависимостей, ~1KB кода

**Когда стоит выбрать альтернативы:**
- **Data-атрибуты** — если нужна гибкость (поле видимо для нескольких типов) или name может меняться
- **CSS классы** — если важна производительность на больших формах (100+ полей)
- **Alpine.js** — если в проекте уже используется Alpine или нужны сложные условия видимости

---

## Использование jQuery (опционально)

```javascript
(function($) {
    function initFieldVisibility() {
        const $select = $('select[name="type"]');
        
        function updateFields() {
            const selectedValue = $select.val();
            
            $('input[name]').each(function() {
                const $input = $(this);
                const fieldName = $input.attr('name');
                
                $input.toggle(fieldName.includes(selectedValue));
            });
        }
        
        $select.on('change', updateFields);
        updateFields();
    }
    
    $(document).ready(initFieldVisibility);
})(jQuery);
```

**Преимущества jQuery в данном случае:**
- Кроссбраузерная совместимость (IE8+)
- Лаконичный синтаксис: `$input.toggle()` вместо `input.style.display = ...`
- Встроенная обработка коллекций без forEach
- Удобная работа с событиями

**Недостатки:**
- Внешняя зависимость ~30KB (минифицированная)
- Избыточно для современных браузеров (ES6+)
- В 2026 году Vanilla JS имеет все необходимые API

**Вывод:** jQuery оправдан только если:
1. Проект уже использует jQuery
2. Требуется поддержка старых браузеров (IE11 и ниже)
3. В проекте много DOM-манипуляций, где jQuery упрощает код

---

## Инструкция по тестированию в консоли браузера

### Вариант 1: Быстрый тест (создание формы в консоли)

Скопируйте и вставьте в консоль Chrome/Firefox:

```javascript
// Создаем тестовую форму
document.body.innerHTML = `
    <select name="type" id="type">
        <option value="">-- Выберите --</option>
        <option value="personal">Личные данные</option>
        <option value="company">Компания</option>
    </select>
    <br><br>
    <input type="text" name="personal_name" placeholder="Имя (personal_name)"><br>
    <input type="email" name="personal_email" placeholder="Email (personal_email)"><br>
    <input type="text" name="company_name" placeholder="Компания (company_name)"><br>
    <input type="text" name="company_inn" placeholder="ИНН (company_inn)"><br>
`;

// Вставляем скрипт
(function() {
    'use strict';
    
    function initFieldVisibilityController(selectSelector = 'select[name="type"]', inputSelector = 'input[name]') {
        const selectElement = document.querySelector(selectSelector);
        
        if (!selectElement) {
            console.warn('Select element not found');
            return;
        }
        
        function updateFieldVisibility() {
            const selectedValue = selectElement.value;
            const inputFields = document.querySelectorAll(inputSelector);
            
            inputFields.forEach(input => {
                const fieldName = input.getAttribute('name');
                
                if (fieldName && fieldName.includes(selectedValue)) {
                    input.style.display = '';
                } else {
                    input.style.display = 'none';
                }
            });
        }
        
        selectElement.addEventListener('change', updateFieldVisibility);
        updateFieldVisibility();
    }
    
    initFieldVisibilityController();
})();

console.log('✅ Форма создана! Выберите значение в select для проверки.');
```

### Вариант 2: Тест на существующей странице

Если на странице уже есть форма с select и input полями:

```javascript
// Скопируйте только этот блок
(function() {
    'use strict';
    
    const selectElement = document.querySelector('select[name="type"]');
    
    if (!selectElement) {
        console.error('❌ Select с name="type" не найден на странице');
        return;
    }
    
    function updateFieldVisibility() {
        const selectedValue = selectElement.value;
        const inputFields = document.querySelectorAll('input[name]');
        
        console.log(`🔍 Выбрано значение: "${selectedValue}"`);
        console.log(`📋 Найдено полей: ${inputFields.length}`);
        
        let visibleCount = 0;
        
        inputFields.forEach(input => {
            const fieldName = input.getAttribute('name');
            
            if (fieldName && fieldName.includes(selectedValue)) {
                input.style.display = '';
                visibleCount++;
                console.log(`✅ Показано: ${fieldName}`);
            } else {
                input.style.display = 'none';
                console.log(`❌ Скрыто: ${fieldName}`);
            }
        });
        
        console.log(`📊 Видимых полей: ${visibleCount}/${inputFields.length}`);
    }
    
    selectElement.addEventListener('change', updateFieldVisibility);
    updateFieldVisibility();
    
    console.log('✅ Скрипт инициализирован! Измените значение в select.');
})();
```

### Вариант 3: Открыть demo.html

1. Откройте файл `public/demo.html` в браузере
2. Откройте DevTools (F12)
3. Выберите значение в выпадающем списке "Тип"
4. Наблюдайте, как поля показываются/скрываются

### Проверка работы:

1. **При загрузке страницы** — все поля должны быть скрыты (если select пустой)
2. **При выборе "Личные данные"** — показываются только поля с `personal` в name
3. **При выборе "Компания"** — показываются только поля с `company` в name
4. **При смене значения** — видимость обновляется мгновенно

### Отладка:

Если что-то не работает, проверьте в консоли:

```javascript
// Проверка наличия select
console.log(document.querySelector('select[name="type"]'));

// Проверка всех input полей
console.log(document.querySelectorAll('input[name]'));

// Проверка текущего значения select
console.log(document.querySelector('select[name="type"]').value);
```

---

## Файлы проекта

- `public/js/field-visibility.js` — основной скрипт
- `public/demo.html` — демонстрационная страница
- `FIELD_VISIBILITY_ANALYSIS.md` — этот документ

## Использование в проекте

### Подключение к HTML:

```html
<script src="js/field-visibility.js"></script>
```

### Ручная инициализация с кастомными селекторами:

```javascript
// После загрузки DOM
initFieldVisibilityController('#mySelect', 'input.dynamic-field');
```
