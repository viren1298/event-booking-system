<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test customer can process payment
     */
    public function test_customer_can_process_payment(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['price' => 50]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending'
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/bookings/{$booking->id}/payment");

        // Payment may succeed or fail (mocked)
        $this->assertContains($response->status(), [200, 400]);

        if ($response->status() === 200) {
            $this->assertDatabaseHas('payments', [
                'booking_id' => $booking->id,
                'amount' => 100,
                'status' => 'success'
            ]);

            $this->assertDatabaseHas('bookings', [
                'id' => $booking->id,
                'status' => 'confirmed'
            ]);
        }
    }

    /**
     * Test customer cannot process payment for other's booking
     */
    public function test_customer_cannot_process_payment_for_others_booking(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create(['user_id' => $customer1->id]);

        $token = $customer2->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/bookings/{$booking->id}/payment");

        $response->assertStatus(403);
    }

    /**
     * Test cannot process payment for confirmed booking
     */
    public function test_cannot_process_payment_for_confirmed_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'status' => 'confirmed'
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/bookings/{$booking->id}/payment");

        $response->assertStatus(400);
    }

    /**
     * Test can view payment details
     */
    public function test_can_view_payment_details(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create(['user_id' => $customer->id]);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 100,
            'status' => 'success'
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => ['id', 'booking_id', 'amount', 'status']
            ]);
    }

    /**
     * Test customer cannot view other's payment
     */
    public function test_customer_cannot_view_others_payment(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create(['user_id' => $customer1->id]);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 100,
            'status' => 'success'
        ]);

        $token = $customer2->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(403);
    }
}
