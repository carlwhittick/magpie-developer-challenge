<?php

namespace App\Classes;

use App\Enums\FileSizeUnit;

class FileSize
{
    private float $sizeInBytes;

    /**
     * Constructor accepts either a size string (e.g., '2 GB') or size and unit.
     * 
     * @param string|float $size
     * @param FileSizeUnit|null $unit
     */
    public function __construct($size, ?FileSizeUnit $unit = null)
    {
        if (is_string($size)) {
            // Parse the string format like "2 GB"
            $this->parseSizeString($size);
        } else {
            // If two parameters are passed (size and unit)
            $this->sizeInBytes = $this->convertToBytes($size, $unit);
        }
    }

    /**
     * Parse a size string (e.g., "2 GB") and initialize the size in bytes.
     */
    private function parseSizeString(string $sizeString): void
    {
        // Match number and unit (e.g., 2 GB or 500 MB)
        preg_match('/(\d+(\.\d+)?)\s*([a-zA-Z]+)/', $sizeString, $matches);

        if (count($matches) !== 4) {
            throw new \InvalidArgumentException("Invalid size string format");
        }

        $size = (float) $matches[1];
        $unit = strtoupper($matches[3]);

        $unitEnum = FileSizeUnit::tryFrom($unit);
        if ($unitEnum === null) {
            throw new \InvalidArgumentException("Invalid unit: {$unit}. Valid units are: " . implode(', ', FileSizeUnit::cases()));
        }

        $this->sizeInBytes = $this->convertToBytes($size, $unitEnum);
    }

    /**
     * Convert a size and unit to bytes.
     */
    private function convertToBytes(float $size, FileSizeUnit $unit): float
    {
        return $size * (1000 ** $unit->exponent());
    }

    /**
     * Return the size in bytes.
     */
    public function getBytes(): float
    {
        return $this->sizeInBytes;
    }

    /**
     * Return the size in KB.
     */
    public function getKilobytes(): float
    {
        return $this->sizeInBytes / 1000;
    }

    /**
     * Return the size in MB.
     */
    public function getMegabytes(): float
    {
        return $this->getKilobytes() / 1000;
    }

    /**
     * Return the size in GB.
     */
    public function getGigabytes(): float
    {
        return $this->getMegabytes() / 1000;
    }

    /**
     * Return the size in TB.
     */
    public function getTerabytes(): float
    {
        return $this->getGigabytes() / 1000;
    }

    /**
     * Return the size in PB.
     */
    public function getPetabytes(): float
    {
        return $this->getTerabytes() / 1000;
    }

    /**
     * Return a human-readable string representation (e.g., "2 GB").
     */
    public function __toString(): string
    {
        return $this->getGigabytes() . ' GB';
    }
}
