<?php

namespace Modules\FAQ\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'question_ar' => $this->question_ar,
            'question_en' => $this->question_en,
            'answer' => $this->answer,
            'answer_ar' => $this->answer_ar,
            'answer_en' => $this->answer_en,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
