/**
 * Dynamic Form Field Visibility Controller
 *
 * Показывает/скрывает input поля на основе выбранного значения в select.
 * Поля отображаются, если их атрибут name содержит выбранное значение.
 */

(function() {
    'use strict';

    /**
     * Инициализирует контроллер видимости полей
     * @param {string} selectSelector - CSS селектор для select элемента
     * @param {string} inputSelector - CSS селектор для input полей (по умолчанию 'input[name]')
     */
    function initFieldVisibilityController(selectSelector = 'select[name="type"]', inputSelector = 'input[name]') {
        const selectElement = document.querySelector(selectSelector);

        if (!selectElement) {
            console.warn(`Select element not found: ${selectSelector}`);
            return;
        }

        /**
         * Обновляет видимость полей на основе выбранного значения
         */
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

        // Вешаем обработчик на изменение select
        selectElement.addEventListener('change', updateFieldVisibility);

        // Применяем логику при загрузке страницы
        updateFieldVisibility();
    }

    // Автоматическая инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initFieldVisibilityController());
    } else {
        initFieldVisibilityController();
    }

    // Экспортируем функцию для ручного вызова
    window.initFieldVisibilityController = initFieldVisibilityController;
})();
