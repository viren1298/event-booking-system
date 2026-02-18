<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
    public function __construct(
        protected PaymentService $service
    ){}

    /**
     * Process payment for a booking
     */
    public function store(Request $request, $bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            // Check if booking belongs to authenticated user
            if ($booking->user_id !== Auth::id()) {
                return $this->error('Unauthorized to process payment for this booking', 403);
            }

            // Check if booking status is pending
            if ($booking->status !== 'pending') {
                return $this->error('Booking is not in pending status', 400);
            }

            $result = $this->service->process($booking);

            if ($result['status'] === 'failed') {
                return $this->error($result['message']);
            }

            return $this->success($result['data'], 'Payment processed successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get payment details
     */
    public function show($id)
    {
        try {
            $payment = Payment::with('booking.user', 'booking.ticket.event')->findOrFail($id);

            // Check if user is authorized to view this payment
            if ($payment->booking->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return $this->error('Unauthorized to view this payment', 403);
            }

            return $this->success($payment);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }
}
