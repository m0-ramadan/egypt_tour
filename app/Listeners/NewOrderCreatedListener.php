<?php

namespace App\Listeners;

use App\Models\ClientsToken;
use App\Events\NewOrder;
use App\Models\Client;
use App\Traits\FirebaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\NewEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NewOrderCreatedListener
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
    public function handle(NewOrder $event): void
    {
        $tokens = ClientsToken::whereIn('client_id', Client::where('type', "!=", "1")->pluck('id')->toArray())->pluck('firebase_id')->toArray();
        $this->sendFirebaseNotification($tokens, "طلب جديد.", "تم انشاء طلب جديد.");
        Notification::send($event->order->client, new NewEmailNotification("تم تغيير حالة طلبك.", "تم انشاء طلب جديد."));
    }
}
