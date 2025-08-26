<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'favorite_id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'added_at' => $this->created_at?->format('Y-m-d H:i:s'),
            
            'product' => $this->when(
                isset($this->product_data),
                function () {
                    if (!$this->product_data) {
                        return [
                            'id' => $this->product_id,
                            'title' => 'Produto temporariamente indisponível',
                            'image' => null,
                            'price' => null,
                            'review' => null,
                            'available' => false
                        ];
                    }

                    return [
                        'id' => $this->product_data['id'],
                        'title' => $this->product_data['title'],
                        'image' => $this->product_data['image'],
                        'price' => $this->product_data['price'],
                        'review' => $this->product_data['review'],
                        'available' => true
                    ];
                }
            )
        ];
    }
}