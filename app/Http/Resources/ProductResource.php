<?php
// app/Http/Resources/ProductResource.php

namespace App\Http\Resources;

use App\Http\Services\FavoriteProductService as ServicesFavoriteProductService;
use App\Services\FavoriteProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'title' => $this->resource['title'],
            'price' => $this->formatPrice($this->resource['price']),
            'description' => $this->resource['description'],
            'category' => [
                'name' => $this->resource['category'],
                'slug' => str_replace(' ', '-', strtolower($this->resource['category']))
            ],
            'image' => [
                'url' => $this->resource['image'],
                'alt' => $this->resource['title']
            ],
            'rating' => $this->formatRating($this->resource['rating'] ?? []),
            'availability' => [
                'in_stock' => true,
                'status' => 'available'
            ],
            'metadata' => [
                'source' => 'fakestoreapi',
                'last_updated' => now()->toISOString()
            ],

            'is_favorite' => $this->when(
                Auth::check() && isset($this->additional['check_favorite']),
                function () {
                    return app(ServicesFavoriteProductService::class)
                        ->isFavorite(Auth::id(), $this->resource['id']);
                }
            ),

        ];
    }

    /**
     * Price formatter
     */
    private function formatPrice(float $price): ?array
    {
        if ($price === null) {
            return null;
        }
        return [
            'raw' => $price,
            'formatted' => 'R$ ' . number_format($price, 2, ',', '.'),
        ];
    }

    /**
     * Formatar rating
     */
    private function formatRating(array $rating): array
    {
        $rate = $rating['rate'] ?? 0;
        $count = $rating['count'] ?? 0;

        return [
            'rate' => $rate,
            'count' => $count,
            'stars' => $this->formatStars($rate),
            'percentage' => $this->ratingToPercentage($rate),
            'quality' => $this->getRatingQuality($rate),
            'display' => $rate > 0 ? "{$rate}/5 ({$count} avaliações)" : 'Sem avaliações'
        ];
    }

    /**
     * Rating stars formatter
     */
    private function formatStars(float $rate): string
    {
        if ($rate <= 0) {
            return str_repeat('☆', 5);
        }

        $fullStars = floor($rate);
        $halfStar = ($rate - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('★', $fullStars) .
            str_repeat('☆', $halfStar) .
            str_repeat('☆', $emptyStars);
    }

    /**
     * Rating to percentage
     */
    private function ratingToPercentage(float $rate): int
    {
        return (int) round(($rate / 5) * 100);
    }

    /**
     * Rating quality getter
     */
    private function getRatingQuality(float $rate): string
    {
        return match (true) {
            $rate >= 4.5 => 'excellent',
            $rate >= 4.0 => 'very_good',
            $rate >= 3.5 => 'good',
            $rate >= 3.0 => 'average',
            $rate >= 2.0 => 'below_average',
            $rate > 0 => 'poor',
            default => 'no_rating'
        };
    }
}
