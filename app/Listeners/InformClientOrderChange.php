<?php

namespace App\Listeners;

use Exception;
use App\Models\Client;
use App\Models\ClientsToken;
use App\Mail\OrderChangeMail;
use App\Events\OrderStatusChanged;
use App\Traits\FirebaseNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\NewEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class InformClientOrderChange
{
    use FirebaseNotification;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        // send email
        try {
            Mail::to($event->order->user_email ?? $event->order->client->email)->send(new OrderChangeMail($event->order));
        } catch (Exception $e) {
            //
        }

        // send notification
        $this->sendFirebaseNotification(
            $event->order->client?->firebaseTokens->pluck('firebase_id')->toArray(),
            "طلبك.",
            "تم تغيير حالة طالبك."
        );

        if ($event->order?->client != null) {
            Notification::send(
                $event->order?->client,
                new NewEmailNotification("طلبك.", "تم تغيير حالة طالبك.")
            );
        }
    }
}
