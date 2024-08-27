<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'cruxId' => $this->cruxId,
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'fullname' => $this->lastname.' '.$this->firstname,
            'email' => $this->email,
            'role' => $this->role,
            'contacts' => $this->contacts,
            'address' => $this->address,
            'ver_code' => $this->ver_code,
            'status' => (bool) $this->status,
            'ev' => (bool) $this->ev,
            'created' => Carbon::parse($this->created_at)->format('d-M-Y'),
        ];
    }
}
