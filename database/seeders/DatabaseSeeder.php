<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 2 admins
        User::factory()->count(2)->create([
            'role' => 'admin'
        ]);

        // Create 3 organizers
        $organizers = User::factory()->count(3)->create([
            'role' => 'organizer'
        ]);

        // Create 10 customers
        $customers = User::factory()->count(10)->create([
            'role' => 'customer'
        ]);

        // Create 5 events with organizers
        $events = Event::factory()
            ->count(5)
            ->sequence(
                ['created_by' => $organizers[0]->id],
                ['created_by' => $organizers[1]->id],
                ['created_by' => $organizers[2]->id],
                ['created_by' => $organizers[0]->id],
                ['created_by' => $organizers[1]->id],
            )
            ->create();

        // Create 15 tickets across events  
        $tickets = [];
        foreach ($events as $event) {
            $eventTickets = Ticket::factory()
                ->count(3)
                ->create([
                    'event_id' => $event->id
                ]);
            $tickets = array_merge($tickets, $eventTickets->toArray());
        }

        // Create 20 bookings with payments
        $allTickets = Ticket::all();
        $bookingCount = 0;
        
        foreach ($customers->take(10) as $index => $customer) {
            $customerTickets = $allTickets->random(min(2, count($allTickets)));
            
            foreach ($customerTickets as $ticket) {
                if ($bookingCount < 20) {
                    $booking = Booking::create([
                        'user_id' => $customer->id,
                        'ticket_id' => $ticket->id,
                        'quantity' => rand(1, 3),
                        'status' => 'confirmed'
                    ]);

                    // Create payment for booking
                    Payment::create([
                        'booking_id' => $booking->id,
                        'amount' => $ticket->price * $booking->quantity,
                        'status' => 'success'
                    ]);

                    $bookingCount++;
                }
            }
        }
    }
}
