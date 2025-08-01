<?php

namespace App\Listeners;

use App\Events\StoreDriverBehavior;
use App\Services\DriverBehaviorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreDriverBehaviorListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $driverBehaviorService;

    /**
     * Create the event listener.
     */
    public function __construct(DriverBehaviorService $driverBehaviorService)
    {
        $this->driverBehaviorService = $driverBehaviorService;
    }

    /**
     * Handle the event.
     */
    public function handle(StoreDriverBehavior $event): void
    {
        $this->driverBehaviorService->storeBehavior($event->data);
    }
}
