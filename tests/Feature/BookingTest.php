<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test customer can book a ticket
     */
    public function test_customer_can_book_ticket(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'quantity' => 10
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/tickets/{$ticket->id}/bookings", [
                'quantity' => 2
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'user_id', 'ticket_id', 'quantity', 'status']
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending'
        ]);

        // Check ticket quantity decreased
        $this->assertEquals(8, $ticket->fresh()->quantity);
    }

    /**
     * Test booking fails if not enough tickets
     */
    public function test_booking_fails_if_not_enough_tickets(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'quantity' => 2
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/tickets/{$ticket->id}/bookings", [
                'quantity' => 5
            ]);

        $response->assertStatus(400);
    }

    /**
     * Test prevent double booking middleware
     */
    public function test_prevent_double_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'quantity' => 20
        ]);

        // First booking
        Booking::create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending'
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        // Try second booking for same ticket
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/tickets/{$ticket->id}/bookings", [
                'quantity' => 1
            ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Already booked']);
    }

    /**
     * Test customer can view own bookings
     */
    public function test_customer_can_view_own_bookings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $bookings = Booking::factory()->count(3)->create([
            'user_id' => $customer->id
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id', 'user_id', 'ticket_id', 'quantity', 'status']
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    /**
     * Test customer can cancel own booking
     */
    public function test_customer_can_cancel_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['quantity' => 5]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'confirmed'
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled'
        ]);

        // Check ticket quantity increased
        $this->assertEquals(7, $ticket->fresh()->quantity);
    }

    /**
     * Test customer cannot cancel other's booking
     */
    public function test_customer_cannot_cancel_others_booking(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create(['user_id' => $customer1->id]);

        $token = $customer2->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(403);
    }
}
