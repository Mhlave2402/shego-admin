<?php

namespace Modules\ZoneManagement\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'currency_code' => $this->currency_code,
            'currency_symbol' => $this->currency_symbol,
            'exchange_rate' => $this->exchange_rate,
            'timezone' => $this->timezone,
        ];
    }
}
