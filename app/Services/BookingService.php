<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    public function store($ticketId, $quantity)
    {
        $ticket = Ticket::findOrFail($ticketId);

        if ($ticket->quantity < $quantity) {
            throw new \Exception('Not enough tickets available');
        }

        $ticket->decrement('quantity', $quantity);

        return Booking::create([
            'user_id' => Auth::id(),
            'ticket_id' => $ticketId,
            'quantity' => $quantity,
            'status' => 'pending'
        ]);
    }
}
