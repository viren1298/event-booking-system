<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EventController extends BaseController
{
    public function __construct(
        protected EventService $service
    ){}

    /**
     * Get all events with pagination, search, and filtering
     */
    public function index(Request $request)
    {
        try {
            $query = Event::with('tickets', 'organizer');

            // Search by title
            if ($request->has('search')) {
                $query->searchByTitle($request->search);
            }

            // Filter by date
            if ($request->has('date')) {
                $query->filterByDate($request->date);
            }

            // Filter by location
            if ($request->has('location')) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            $events = $query->paginate($request->get('per_page', 15));

            return $this->success($events);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get a single event with tickets
     */
    public function show($id)
    {
        try {
            $event = Cache::remember("event_{$id}", 3600, function () use ($id) {
                return Event::with('tickets', 'organizer')->findOrFail($id);
            });

            return $this->success($event);
        } catch (\Exception $e) {
            return $this->error('Event not found', 404);
        }
    }

    /**
     * Create a new event (organizer only)
     */
    public function store(StoreEventRequest $request)
    {
        try {
            $event = $this->service->store($request);
            Cache::forget('events_list');
            return $this->success($event, 'Event created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Update an event (organizer only, own events)
     */
    public function update(UpdateEventRequest $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            if ($event->created_by !== Auth::id() && Auth::user()->role !== 'admin') {
                return $this->error('Unauthorized to update this event', 403);
            }

            $event->update($request->validated());
            Cache::forget("event_{$id}");
            Cache::forget('events_list');

            return $this->success($event, 'Event updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Delete an event (organizer only, own events)
     */
    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);

            if ($event->created_by !== Auth::id() && Auth::user()->role !== 'admin') {
                return $this->error('Unauthorized to delete this event', 403);
            }

            $event->delete();
            Cache::forget("event_{$id}");
            Cache::forget('events_list');

            return $this->success(null, 'Event deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

