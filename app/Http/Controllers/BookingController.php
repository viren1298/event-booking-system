<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends BaseController
{
    public function __construct(
        protected BookingService $service
    ){}

    /**
     * Create a booking for a ticket (customer only)
     */
    public function store(Request $request, $ticketId)
    {
        try {
            $ticket = Ticket::findOrFail($ticketId);

            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            // Check if enough tickets available
            if ($ticket->quantity < $validated['quantity']) {
                return $this->error('Not enough tickets available', 400);
            }

            $booking = $this->service->store($ticketId, $validated['quantity']);

            return $this->success($booking, 'Booking created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get all bookings for the authenticated customer
     */
    public function index(Request $request)
    {
        try {
            $bookings = Booking::where('user_id', Auth::id())
                ->with(['ticket.event', 'payment'])
                ->paginate($request->get('per_page', 15));

            return $this->success($bookings);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Cancel a booking (customer only, own bookings)
     */
    public function cancel($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Check if booking belongs to authenticated user
            if ($booking->user_id !== Auth::id()) {
                return $this->error('Unauthorized to cancel this booking', 403);
            }

            // Check if booking can be cancelled
            if ($booking->status !== 'pending' && $booking->status !== 'confirmed') {
                return $this->error('Booking cannot be cancelled in current status', 400);
            }

            // Restore ticket quantity
            $booking->ticket->increment('quantity', $booking->quantity);

            $booking->update(['status' => 'cancelled']);

            return $this->success($booking, 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
