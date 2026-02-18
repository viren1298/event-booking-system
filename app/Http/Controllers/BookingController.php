<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends BaseController
{
    public function __construct(protected BookingService $service){}

    public function store($id, Request $request)
    {
        return $this->success($this->service->store($id, $request->quantity));
    }
}
