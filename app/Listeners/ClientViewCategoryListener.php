<?php

namespace App\Listeners;

use App\Models\ClientCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientViewCategoryListener
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
    public function handle(object $event): void
    {
        try {
            if (auth('clients')->id()) {
                ClientCategory::firstOrCreate([
                    'client_id' => auth('clients')->id(),
                    'category_id' => $event->category_id
                ]);
            }
        } catch (\Throwable $th) {
        }
    }
}
