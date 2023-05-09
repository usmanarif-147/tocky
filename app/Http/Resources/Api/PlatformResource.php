<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PlatformResource extends JsonResource
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
            'id' => $this->id ?? null,
            'title' => $this->title ?? null,
            'icon' => $this->icon ?? null,
            'input' => $this->input ?? null,
            'baseUrl' => $this->baseUrl ?? null,
            'created_at' => defaultDateFormat($this->created_at) ?? null,
            'path' => $this->path ?? null,
            'label' => $this->label ?? null,
            'direct' => $this->direct ?? null,
        ];
    }
}
