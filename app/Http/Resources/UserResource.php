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
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'image' => $this->image,
            'created_at' => Carbon::parse($this->created_at)->format('d/m/Y H:i'),
            'publish' => $this->publish,
            'user_catalogue_id' => $this->user_catalogue_id,
            'province_id' => $this->province_id,
            'district_id' => $this->district_id,
            'ward_id' => $this->ward_id,

            // Thêm thông tin role
            'role' => $this->role ? [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ] : null,
        ];
    }
}
