<?php

namespace App;

class Product
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
}
