<?php

namespace App\Listeners;

use App\Events\OrderChangeStatus;
use App\Notifications\NewEmailNotification;
use App\Traits\FirebaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class OrderChangeListener
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
    public function handle(OrderChangeStatus $event): void
    {
        $tokens = $event->order->client->firebaseTokens->pluck('firebase_id')->toArray();
        $message = match ((string)$event->order->status) {
            "0" => 'جارى تجهيز الطلب.',
            "1" => 'جارى تجهيز الطلب.',
            "2" => 'جارى توصيل الطلب.',
            "3" => 'تم توصيل الطلب.',
            "4" => 'الطلب معلق لبعض المشاكل سوف يتم حلها قريبا.',
            default => 'الطلب معلق لبعض المشاكل سوف يتم حلها قريبا.',
        };
        if($tokens){
        $this->sendFirebaseNotification($tokens, "تم تغيير حالة طلبك.", $message);
        Notification::send($event->order->client, new NewEmailNotification("تم تغيير حالة طلبك.", $message));
        }
    }
}
