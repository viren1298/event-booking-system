<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test get all events
     */
    public function test_can_get_all_events(): void
    {
        Event::factory()->count(5)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'description', 'date', 'location', 'created_by']
                    ]
                ]
            ]);
    }

    /**
     * Test get single event with tickets
     */
    public function test_can_get_single_event(): void
    {
        $event = Event::factory()->create();
        Ticket::factory()->count(3)->create(['event_id' => $event->id]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id', 'title', 'description', 'date', 'location',
                    'tickets' => ['*' => ['id', 'type', 'price', 'quantity']]
                ]
            ]);
    }

    /**
     * Test event search
     */
    public function test_can_search_events_by_title(): void
    {
        Event::factory()->create(['title' => 'Laravel Conference']);
        Event::factory()->create(['title' => 'PHP Workshop']);

        $response = $this->getJson('/api/events?search=Laravel');

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.title', 'Laravel Conference');
    }

    /**
     * Test organizer can create event
     */
    public function test_organizer_can_create_event(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $token = $organizer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/events', [
                'title' => 'Tech Meetup',
                'description' => 'Annual tech meetup',
                'date' => now()->addDays(30)->toDateTimeString(),
                'location' => 'New York',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Tech Meetup');

        $this->assertDatabaseHas('events', [
            'title' => 'Tech Meetup',
            'created_by' => $organizer->id
        ]);
    }

    /**
     * Test customer cannot create event
     */
    public function test_customer_cannot_create_event(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/events', [
                'title' => 'Tech Meetup',
                'description' => 'Annual tech meetup',
                'date' => now()->addDays(30)->toDateTimeString(),
                'location' => 'New York',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test organizer can update own event
     */
    public function test_organizer_can_update_own_event(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $token = $organizer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/events/{$event->id}", [
                'title' => 'Updated Tech Meetup',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Tech Meetup'
        ]);
    }

    /**
     * Test organizer cannot update other's event
     */
    public function test_organizer_cannot_update_others_event(): void
    {
        $organizer1 = User::factory()->create(['role' => 'organizer']);
        $organizer2 = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer1->id]);
        $token = $organizer2->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/events/{$event->id}", [
                'title' => 'Updated Tech Meetup',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test organizer can delete own event
     */
    public function test_organizer_can_delete_own_event(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $token = $organizer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
