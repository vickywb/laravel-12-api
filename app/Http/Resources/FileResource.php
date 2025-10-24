<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_url' => $this->file_url,
            'upload_at' => $this->upload_at->format('d-m-Y H:i:s'),
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'mime_type' => $this->mime_type,
        ];
    }
}