<?php

namespace Tests\Feature;

use App\Models\Joke;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JokeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_jokes_endpoint_returns_all_jokes_as_json(): void
    {
        Joke::create([
            'external_id' => 1,
            'type' => 'general',
            'setup' => 'Why did the chicken cross the road?',
            'punchline' => 'To get to the other side!',
        ]);

        Joke::create([
            'external_id' => 2,
            'type' => 'programming',
            'setup' => 'Why do programmers prefer dark mode?',
            'punchline' => 'Because light attracts bugs!',
        ]);

        $response = $this->getJson('/api/jokes');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'external_id',
                    'type',
                    'setup',
                    'punchline',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_jokes_endpoint_returns_empty_array_when_no_jokes(): void
    {
        $response = $this->getJson('/api/jokes');

        $response->assertStatus(200)
            ->assertJson([]);
    }
}
