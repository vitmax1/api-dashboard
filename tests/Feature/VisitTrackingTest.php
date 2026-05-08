<?php

namespace Tests\Feature;

use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_endpoint_stores_visit_data(): void
    {
        $response = $this->postJson('/api/track', [
            'device' => 'Desktop',
            'city' => 'Moscow',
            'payload' => [
                'userAgent' => 'Mozilla/5.0',
                'language' => 'ru-RU',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Visit recorded successfully',
            ]);

        $this->assertDatabaseHas('visits', [
            'device' => 'Desktop',
            'city' => 'Moscow',
        ]);
    }

    public function test_track_endpoint_validates_device_type(): void
    {
        $response = $this->postJson('/api/track', [
            'device' => 'InvalidDevice',
            'city' => 'Moscow',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device']);
    }

    public function test_track_endpoint_requires_device_field(): void
    {
        $response = $this->postJson('/api/track', [
            'city' => 'Moscow',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device']);
    }

    public function test_track_endpoint_accepts_mobile_device(): void
    {
        $response = $this->postJson('/api/track', [
            'device' => 'Mobile',
            'city' => 'Saint Petersburg',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('visits', [
            'device' => 'Mobile',
            'city' => 'Saint Petersburg',
        ]);
    }

    public function test_track_endpoint_stores_ip_address(): void
    {
        $response = $this->postJson('/api/track', [
            'device' => 'Desktop',
            'city' => 'Moscow',
        ]);

        $response->assertStatus(201);

        $visit = Visit::latest()->first();
        $this->assertNotNull($visit->ip);
    }

    public function test_track_endpoint_accepts_null_city(): void
    {
        $response = $this->postJson('/api/track', [
            'device' => 'Desktop',
            'city' => null,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('visits', [
            'device' => 'Desktop',
            'city' => null,
        ]);
    }
}
