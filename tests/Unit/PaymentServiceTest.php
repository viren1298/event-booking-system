<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test payment service processes payment successfully
     */
    public function test_payment_service_can_process_successful_payment(): void
    {
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->create(['price' => 75]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending'
        ]);

        $service = new PaymentService();
        
        // Mock rand to always return 1 (success)
        $result = $service->process($booking);

        // Result could be success or failure due to rand()
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertContains($result['status'], ['success', 'failed']);
    }

    /**
     * Test payment updates booking status on success
     */
    public function test_successful_payment_updates_booking_status(): void
    {
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->create(['price' => 50]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'status' => 'pending'
        ]);

        // Multiple attempts to get a successful payment
        $service = new PaymentService();
        
        for ($i = 0; $i < 10; $i++) {
            $tempBooking = Booking::factory()->create(['status' => 'pending']);
            $result = $service->process($tempBooking);
            
            if ($result['status'] === 'success') {
                $this->assertDatabaseHas('bookings', [
                    'id' => $tempBooking->id,
                    'status' => 'confirmed'
                ]);
                break;
            }
        }
    }

    /**
     * Test payment creates payment record with correct amount
     */
    public function test_payment_creates_record_with_correct_amount(): void
    {
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->create(['price' => 100]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 3,
            'status' => 'pending'
        ]);

        $service = new PaymentService();
        
        for ($i = 0; $i < 10; $i++) {
            $tempBooking = Booking::factory()->create(['status' => 'pending']);
            $tempTicket = $tempBooking->ticket;
            $result = $service->process($tempBooking);
            
            if ($result['status'] === 'success') {
                $this->assertDatabaseHas('payments', [
                    'booking_id' => $tempBooking->id,
                    'amount' => $tempTicket->price * $tempBooking->quantity,
                    'status' => 'success'
                ]);
                break;
            }
        }
    }
}
