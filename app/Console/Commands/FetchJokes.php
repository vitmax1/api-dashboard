<?php

namespace App\Console\Commands;

use App\Models\Joke;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchJokes extends Command
{
    protected $signature = 'app:fetch-jokes';
    protected $description = 'Fetch a random joke from external API and store it in database';

    public function handle(): int
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->get('https://official-joke-api.appspot.com/random_joke');

            if ($response->failed()) {
                $this->error('Failed to fetch joke from API');
                return self::FAILURE;
            }

            $data = $response->json();

            Joke::updateOrCreate(
                ['external_id' => $data['id']],
                [
                    'type' => $data['type'],
                    'setup' => $data['setup'],
                    'punchline' => $data['punchline'],
                ]
            );

            $this->info('Joke fetched and stored successfully');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
