<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Post extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'seat'           => 6,
            'subject'        => $this->subject,
            'departure_date' => Carbon::parse($this->departure_date)->format('m/d'),
            'departure'      => $this->departure,
            'destination'    => $this->destination,
            'description'    => $this->description,
            'ptt_url'        => $this->ptt_url,
            'type'           => $this->type,
            'created_at'     => $this->created_at
        ];
    }
}
