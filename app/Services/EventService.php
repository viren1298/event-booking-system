<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Auth;

class EventService
{
    public function __construct(
        protected EventRepository $repo
    ){}

    public function index($request)
    {
        return $this->repo->list($request->all());
    }

    public function store($request)
    {
        return $this->repo->create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);
    }
}
