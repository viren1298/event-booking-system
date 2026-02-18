<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends BaseController
{
    /**
     * Create a ticket for an event (organizer only)
     */
    public function store(Request $request, $eventId)
    {
        try {
            $event = Event::findOrFail($eventId);

            // Check if user is organizer of this event or admin
            if ($event->created_by !== Auth::id() && Auth::user()->role !== 'admin') {
                return $this->error('Unauthorized to add tickets to this event', 403);
            }

            $validated = $request->validate([
                'type' => 'required|string|max:50',
                'price' => 'required|numeric|min:0.01',
                'quantity' => 'required|integer|min:1',
            ]);

            $ticket = Ticket::create([
                'event_id' => $eventId,
                'type' => $validated['type'],
                'price' => $validated['price'],
                'quantity' => $validated['quantity'],
            ]);

            return $this->success($ticket, 'Ticket created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Update a ticket (organizer only, own events)
     */
    public function update(Request $request, $id)
    {
        try {
            $ticket = Ticket::with('event')->findOrFail($id);

            // Check if user is organizer of the event or admin
            if ($ticket->event->created_by !== Auth::id() && Auth::user()->role !== 'admin') {
                return $this->error('Unauthorized to update this ticket', 403);
            }

            $validated = $request->validate([
                'type' => 'sometimes|string|max:50',
                'price' => 'sometimes|numeric|min:0.01',
                'quantity' => 'sometimes|integer|min:0',
            ]);

            $ticket->update($validated);

            return $this->success($ticket, 'Ticket updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Delete a ticket (organizer only, own events)
     */
    public function destroy($id)
    {
        try {
            $ticket = Ticket::with('event')->findOrFail($id);

            // Check if user is organizer of the event or admin
            if ($ticket->event->created_by !== Auth::id() && Auth::user()->role !== 'admin') {
                return $this->error('Unauthorized to delete this ticket', 403);
            }

            $ticket->delete();

            return $this->success(null, 'Ticket deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

