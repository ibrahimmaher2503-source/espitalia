<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'body' => $this->body,
            'body_ar' => $this->body_ar,
            'body_en' => $this->body_en,
            'data' => $this->data,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
