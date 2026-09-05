<?php

namespace App\Listeners;

use App\Events\NewOrder;
use App\Mail\OrderDetailsMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class InformOrderClient
{
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
        Mail::to($event->order->user_email)->send(new OrderDetailsMail($event->order));
    }
}
