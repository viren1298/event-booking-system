<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends BaseController
{
    public function __construct(
        protected EventService $service
    ){}

    public function index(Request $request)
    {
        return $this->success($this->service->index($request));
    }

    public function store(StoreEventRequest $request)
    {
        return $this->success($this->service->store($request));
    }
}
