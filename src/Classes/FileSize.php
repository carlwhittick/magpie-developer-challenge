<?php

namespace App\Classes;

use App\Enums\FileSizeUnit;

class FileSize
{
    /**
     * The size of the file in bytes. 
     * This property is readonly to ensure immutability after initialization.
     */
    private readonly float $sizeInBytes;

    /**
     * Constructor accepts either a size string (e.g., '2 GB', '4MB') or size and unit.
     * 
     * @param string|float $size The size of the file (string or float).
     * @param FileSizeUnit|null $unit The unit of the file size (optional). Defaults to bytes if not provided.
     */
    public function __construct(string|float $size, ?FileSizeUnit $unit = null)
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
     *
     * @param string $sizeString The size string to parse (e.g., "2 GB").
     * 
     * @throws \InvalidArgumentException if the size string format is invalid.
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
     *
     * @param float $size The size value.
     * @param FileSizeUnit $unit The unit of the size (e.g., KB, MB, GB).
     * 
     * @return float The size in bytes.
     */
    private function convertToBytes(float $size, FileSizeUnit $unit): float
    {
        return $size * (1000 ** $unit->exponent());
    }

    /**
     * Return the size in bytes.
     *
     * @return float The size in bytes.
     */
    public function getBytes(): float
    {
        return $this->sizeInBytes;
    }

    /**
     * Return the size in KB.
     *
     * @return float The size in kilobytes.
     */
    public function getKilobytes(): float
    {
        return $this->sizeInBytes / 1000;
    }

    /**
     * Return the size in MB.
     *
     * @return float The size in megabytes.
     */
    public function getMegabytes(): float
    {
        return $this->getKilobytes() / 1000;
    }

    /**
     * Return the size in GB.
     *
     * @return float The size in gigabytes.
     */
    public function getGigabytes(): float
    {
        return $this->getMegabytes() / 1000;
    }

    /**
     * Return the size in TB.
     *
     * @return float The size in terabytes.
     */
    public function getTerabytes(): float
    {
        return $this->getGigabytes() / 1000;
    }

    /**
     * Return the size in PB.
     *
     * @return float The size in petabytes.
     */
    public function getPetabytes(): float
    {
        return $this->getTerabytes() / 1000;
    }

    /**
     * Return a human-readable string representation (e.g., "32000 MB").
     *
     * @return string The human-readable file size string.
     */
    public function __toString(): string
    {
        return $this->getMegabytes() . ' MB';
    }
}
