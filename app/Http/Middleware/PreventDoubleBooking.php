<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleBooking
{
    public function handle($request,Closure $next)
    {
        $exists = Booking::where('user_id', Auth::id())
            ->where('ticket_id',$request->ticket_id)
            ->where('status','pending')
            ->exists();

        if($exists){
            return response()->json(['error'=>'Already booked'],400);
        }

        return $next($request);
    }
}