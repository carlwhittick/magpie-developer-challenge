<?php

namespace App\Classes;

use App\Classes\FileSize;
use App\Classes\Cost;
use Carbon\Carbon;

class Product implements \JsonSerializable
{
    public function __construct(
        protected readonly string $title,
        protected readonly Cost $price,
        protected readonly string $imageUrl,
        protected readonly FileSize $capacity,
        protected readonly string $colour,
        protected readonly string $availabilityText,
        protected readonly bool $isAvailable,
        protected readonly string $shippingText,
        protected readonly Carbon|null $shippingDate,
    ) {}

    public function jsonSerialize(): array
    {
        $data = [
            'title' => $this->title,
            'price' => $this->price->getCurrencyValue(),
            'imageUrl' => $this->imageUrl,
            'capacityMB' => $this->capacity->getMegabytes(),
            'colour' => $this->colour,
            'availabilityText' => $this->availabilityText,
            'isAvailable' => $this->isAvailable,
        ];

        if ($this->shippingText) {
            $data['shippingText'] = $this->shippingText;
        }

        if ($this->shippingDate) {
            $data['shippingDate'] = $this->shippingDate->toDateString();
        }

        return $data;
    }
}
