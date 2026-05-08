<?php

namespace Database\Seeders;

use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $cities = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Казань', 'Нижний Новгород', 'Челябинск', 'Самара', 'Омск', 'Ростов-на-Дону'];
        $devices = ['mobile', 'desktop'];

        // Генерируем визиты за последние 24 часа
        for ($i = 0; $i < 100; $i++) {
            $hoursAgo = rand(0, 23);
            $minutesAgo = rand(0, 59);

            Visit::create([
                'ip' => $this->generateRandomIp(),
                'city' => $cities[array_rand($cities)],
                'device' => $devices[array_rand($devices)],
                'payload' => [
                    'user_agent' => $this->generateUserAgent(),
                    'referer' => 'https://google.com',
                ],
                'created_at' => now()->subHours($hoursAgo)->subMinutes($minutesAgo),
            ]);
        }
    }

    private function generateRandomIp(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255);
    }

    private function generateUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'Mozilla/5.0 (Linux; Android 11; SM-G991B)',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
