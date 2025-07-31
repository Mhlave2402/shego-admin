<?php

namespace Modules\ZoneManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ZoneManagement\Service\Interface\ZoneServiceInterface;

class ZoneMiddleware
{
    protected $zoneService;

    public function __construct(ZoneServiceInterface $zoneService)
    {
        $this->zoneService = $zoneService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $request->has('latitude') && $request->has('longitude')) {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            $zone = $this->zoneService->getZoneByCoordinates($latitude, $longitude);

            if ($zone && $user->zone_id !== $zone->id) {
                $user->zone_id = $zone->id;
                $user->save();
            }
        }

        return $next($request);
    }
}
