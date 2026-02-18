<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class EventRepository
{
    public function list($filters)
    {
        return Cache::remember('events_list',60,function() use ($filters){
            return Event::with('tickets')
                ->when($filters['date'] ?? null,
                    fn($q,$date)=>$q->filterByDate($date))
                ->when($filters['search'] ?? null,
                    fn($q,$search)=>$q->searchByTitle($search))
                ->paginate(10);
        });
    }

    public function create($data)
    {
        return Event::create($data);
    }

    public function update($event,$data)
    {
        $event->update($data);
        return $event;
    }

    public function delete($event)
    {
        return $event->delete();
    }
}
