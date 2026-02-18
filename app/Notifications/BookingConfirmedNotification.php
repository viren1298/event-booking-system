<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Booking $booking)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Booking Confirmed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking for ' . $this->booking->ticket->event->title . ' has been confirmed.')
            ->line('Booking ID: ' . $this->booking->id)
            ->line('Quantity: ' . $this->booking->quantity)
            ->line('Total Amount: $' . ($this->booking->ticket->price * $this->booking->quantity))
            ->action('View Booking', url('/bookings/' . $this->booking->id))
            ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'event_title' => $this->booking->ticket->event->title,
            'quantity' => $this->booking->quantity,
            'amount' => $this->booking->ticket->price * $this->booking->quantity,
        ];
    }
}
