/**
 * Lightweight Visit Tracker
 *
 * Автономный JS-скрипт для отслеживания посещений.
 * Определяет тип устройства, получает город по IP и отправляет данные на сервер.
 *
 * Использование:
 * <script src="https://your-domain.com/tracker.js" data-api-url="https://your-domain.com/api/track"></script>
 */

(function() {
    'use strict';

    /**
     * Определяет тип устройства на основе User Agent
     * @returns {string} 'Mobile' или 'Desktop'
     */
    function detectDevice() {
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;

        // Проверка на мобильные устройства
        const mobileRegex = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet/i;

        return mobileRegex.test(userAgent.toLowerCase()) ? 'Mobile' : 'Desktop';
    }

    /**
     * Получает город пользователя через IP API
     * @returns {Promise<string|null>} Название города или null
     */
    async function getCity() {
        try {
            // Используем бесплатное API ip-api.com (лимит: 45 запросов/минуту)
            const response = await fetch('http://ip-api.com/json/?fields=city', {
                method: 'GET',
                cache: 'no-cache',
            });

            if (!response.ok) {
                console.warn('[Tracker] Failed to fetch city from IP API');
                return null;
            }

            const data = await response.json();
            return data.city || null;

        } catch (error) {
            console.warn('[Tracker] Error fetching city:', error.message);
            return null;
        }
    }

    /**
     * Отправляет данные о посещении на сервер
     * @param {string} apiUrl - URL API endpoint
     * @param {object} data - Данные для отправки
     */
    async function sendVisitData(apiUrl, data) {
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
                mode: 'cors',
            });

            if (!response.ok) {
                console.warn('[Tracker] Failed to send visit data:', response.status);
                return;
            }

            const result = await response.json();
            console.log('[Tracker] Visit recorded:', result);

        } catch (error) {
            console.warn('[Tracker] Error sending visit data:', error.message);
        }
    }

    /**
     * Инициализирует трекер
     */
    async function initTracker() {
        // Получаем URL API из data-атрибута скрипта
        const scriptTag = document.currentScript || document.querySelector('script[data-api-url]');

        if (!scriptTag) {
            console.error('[Tracker] Script tag not found. Make sure to add data-api-url attribute.');
            return;
        }

        const apiUrl = scriptTag.getAttribute('data-api-url');

        if (!apiUrl) {
            console.error('[Tracker] API URL not specified. Add data-api-url="https://your-domain.com/api/track" to script tag.');
            return;
        }

        // Определяем устройство
        const device = detectDevice();

        // Получаем город (асинхронно)
        const city = await getCity();

        // Собираем дополнительные данные
        const payload = {
            userAgent: navigator.userAgent,
            language: navigator.language,
            screenResolution: `${screen.width}x${screen.height}`,
            referrer: document.referrer || 'direct',
            timestamp: new Date().toISOString(),
        };

        // Формируем данные для отправки
        const visitData = {
            device: device,
            city: city,
            payload: payload,
        };

        // Отправляем данные
        await sendVisitData(apiUrl, visitData);
    }

    // Запускаем трекер после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTracker);
    } else {
        // DOM уже загружен
        initTracker();
    }

})();
