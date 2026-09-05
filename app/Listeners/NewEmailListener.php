<?php

namespace App\Listeners;

use App\Events\NewEmail;
use App\Notifications\NewEmailNotification;
use App\Traits\FirebaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NewEmailListener
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
    public function handle(NewEmail $event): void
    {
        $tokens = $event->email->reserver->firebaseTokens->pluck('firebase_id')->toArray();
        $title = $event->email->sender->name;
        $content = $event->email->content;
        $this->sendFirebaseNotification($tokens, $title, $content);
        Notification::send($event->email->reserver, new NewEmailNotification($title, $content));
    }
}
