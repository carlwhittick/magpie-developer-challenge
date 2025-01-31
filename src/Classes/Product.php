<?php

namespace App\Classes;

class Product implements \JsonSerializable
{
    public function __construct(
        protected string $title,
        protected float $price,
        protected string $imageUrl,
        protected int $capacityMB,
        protected string $colour,
        protected string $availabilityText,
        protected bool $isAvailable,
        protected string $shippingText,
        protected string $shippingDate,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'price' => $this->price,
            'imageUrl' => $this->imageUrl,
            'capacityMB' => $this->capacityMB,
            'colour' => $this->colour,
            'availabilityText' => $this->availabilityText,
            'isAvailable' => $this->isAvailable,
            'shippingText' => $this->shippingText,
            'shippingDate' => $this->shippingDate,
        ];
    }
}
