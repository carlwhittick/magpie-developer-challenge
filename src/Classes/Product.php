<?php

namespace App\Classes;

use App\Classes\FileSize;
use App\Classes\Cost;
use Carbon\Carbon;

class Product implements \JsonSerializable
{
    /**
     * Product constructor.
     * 
     * Initializes the product's details, including its title, price, image URL, capacity,
     * color, availability, and shipping information.
     *
     * @param string $title The product's title.
     * @param Cost $price The product's price, wrapped in a Cost object.
     * @param string $imageUrl The full URL to the product's image.
     * @param FileSize $capacity The product's capacity, wrapped in a FileSize object.
     * @param string $colour The product's color.
     * @param string $availabilityText The product's availability text.
     * @param bool $isAvailable Whether the product is available or not.
     * @param string $shippingText Text providing shipping information.
     * @param Carbon|null $shippingDate The date when the product will ship, or null if unavailable.
     */
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

    /**
     * Serializes the product into an array for JSON output.
     *
     * Converts the product's properties into a format that can be easily represented as JSON.
     * It includes essential properties such as title, price, capacity in MB, color, availability, 
     * and optional shipping details if available.
     *
     * @return array The serialized product data.
     */
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
