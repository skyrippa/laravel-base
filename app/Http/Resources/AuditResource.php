<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'event'      => $this->event,
            'ip_address' => $this->ip_address,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'user'       => [
                'id'    => $this->user->id ?? null,
                'name'  => $this->user->name ?? null,
                'phone' => $this->user->phone ?? null,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
