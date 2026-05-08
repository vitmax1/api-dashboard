<?php

namespace Tests\Feature;

use App\Models\Joke;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchJokesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_jokes_command_stores_joke_in_database(): void
    {
        Http::fake([
            'https://official-joke-api.appspot.com/random_joke' => Http::response([
                'id' => 123,
                'type' => 'general',
                'setup' => 'Why did the chicken cross the road?',
                'punchline' => 'To get to the other side!',
            ], 200),
        ]);

        $this->artisan('app:fetch-jokes')
            ->assertSuccessful();

        $this->assertDatabaseHas('jokes', [
            'external_id' => 123,
            'type' => 'general',
            'setup' => 'Why did the chicken cross the road?',
            'punchline' => 'To get to the other side!',
        ]);
    }

    public function test_fetch_jokes_command_does_not_duplicate_existing_joke(): void
    {
        Joke::create([
            'external_id' => 123,
            'type' => 'general',
            'setup' => 'Old setup',
            'punchline' => 'Old punchline',
        ]);

        Http::fake([
            'https://official-joke-api.appspot.com/random_joke' => Http::response([
                'id' => 123,
                'type' => 'programming',
                'setup' => 'New setup',
                'punchline' => 'New punchline',
            ], 200),
        ]);

        $this->artisan('app:fetch-jokes')
            ->assertSuccessful();

        $this->assertDatabaseCount('jokes', 1);

        $this->assertDatabaseHas('jokes', [
            'external_id' => 123,
            'type' => 'programming',
            'setup' => 'New setup',
            'punchline' => 'New punchline',
        ]);
    }
}
