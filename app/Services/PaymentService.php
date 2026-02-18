<?php

Namespace App\Services;

use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;

class PaymentService
{
    public function process($booking)
    {
        $success = rand(0,1);

        if(!$success){
            return [
                'status'=>'failed',
                'message'=>'Payment failed'
            ];
        }

        $payment = Payment::create([
            'booking_id'=>$booking->id,
            'amount'=>$booking->ticket->price * $booking->quantity,
            'status'=>'success'
        ]);

        $booking->update(['status'=>'confirmed']);

        $booking->user->notify(
            new BookingConfirmedNotification($booking)
        );

        return [
            'status'=>'success',
            'data'=>$payment
        ];
    }
}
