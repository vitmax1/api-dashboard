<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Статистика посещений</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        header {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            font-size: 24px;
            color: #333;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-card h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .error-message {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4CAF50;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 Статистика посещений</h1>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Выход</button>
            </form>
        </header>

        <div class="error-message" id="errorMessage"></div>

        <div id="loadingIndicator" class="loading">
            <div class="spinner"></div>
            <p>Загрузка статистики...</p>
        </div>

        <div id="chartsContainer" style="display: none;">
            <div class="charts-grid">
                <div class="chart-card">
                    <h2>Уникальные посещения по часам (последние 24 часа)</h2>
                    <div class="chart-container">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h2>Распределение по городам</h2>
                    <div class="chart-container">
                        <canvas id="cityChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h2>Распределение по устройствам</h2>
                <div class="chart-container" style="height: 200px;">
                    <canvas id="deviceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Получаем CSRF токен
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Хранилище для графиков
        let charts = {
            hourly: null,
            city: null,
            device: null
        };

        // Загружаем данные статистики
        async function loadStats() {
            const errorMessage = document.getElementById('errorMessage');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const chartsContainer = document.getElementById('chartsContainer');

            try {
                loadingIndicator.style.display = 'block';
                errorMessage.style.display = 'none';

                const response = await fetch('/api/dashboard/stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    timeout: 30000, // 30 секунд таймаут
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                loadingIndicator.style.display = 'none';
                chartsContainer.style.display = 'block';

                renderCharts(data);

            } catch (error) {
                console.error('Error loading stats:', error);

                loadingIndicator.style.display = 'none';
                errorMessage.textContent = '⚠️ Ошибка загрузки статистики. Сервис может быть в режиме ожидания, попробуйте обновить страницу через 30 секунд.';
                errorMessage.style.display = 'block';

                // Автоматическая повторная попытка через 30 секунд
                setTimeout(loadStats, 30000);
            }
        }

        // Рендерим графики
        function renderCharts(data) {
            // Уничтожаем старые графики перед созданием новых
            if (charts.hourly) charts.hourly.destroy();
            if (charts.city) charts.city.destroy();
            if (charts.device) charts.device.destroy();

            // График по часам (Line Chart)
            const hourlyLabels = data.hourly.map(item => {
                const date = new Date(item.hour);
                return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            });
            const hourlyData = data.hourly.map(item => item.unique_visits);

            charts.hourly = new Chart(document.getElementById('hourlyChart'), {
                type: 'line',
                data: {
                    labels: hourlyLabels,
                    datasets: [{
                        label: 'Уникальные посещения',
                        data: hourlyData,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                            },
                        },
                    },
                },
            });

            // Круговая диаграмма по городам (Pie Chart)
            const cityLabels = data.cities.map(item => item.city || 'Неизвестно');
            const cityData = data.cities.map(item => item.count);

            charts.city = new Chart(document.getElementById('cityChart'), {
                type: 'pie',
                data: {
                    labels: cityLabels,
                    datasets: [{
                        data: cityData,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40',
                            '#FF6384',
                            '#C9CBCF',
                            '#4BC0C0',
                            '#FF6384',
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                    },
                },
            });

            // Диаграмма по устройствам (Doughnut Chart)
            const deviceLabels = data.devices.map(item => item.device);
            const deviceData = data.devices.map(item => item.count);

            charts.device = new Chart(document.getElementById('deviceChart'), {
                type: 'doughnut',
                data: {
                    labels: deviceLabels,
                    datasets: [{
                        data: deviceData,
                        backgroundColor: ['#36A2EB', '#FF6384'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    },
                },
            });
        }

        // Загружаем статистику при загрузке страницы
        loadStats();

        // Автообновление каждые 30 секунд
        setInterval(loadStats, 30000);
    </script>
</body>
</html>
