<?php

namespace App\Classes;

use App\Enums\CurrencySymbol;
use InvalidArgumentException;

class Cost
{
    /**
     * The value of the currency in its smallest unit (e.g., pennies, cents, etc.).
     * For example, £4.40 would be stored as 440 for pounds (pennies), and $4.40 as 440 for dollars (cents).
     */
    private int $smallestUnitValue;

    /**
     * The currency symbol (e.g., '£', '$', '€', '¥', etc.).
     */
    private CurrencySymbol $currencySymbol;

    /**
     * The smallest unit of the currency, e.g., 100 for most currencies (pennies, cents).
     */
    private int $smallestUnit;

    /**
     * Constructor accepts a price string (e.g., '£4.40') and handles different valid currencies.
     *
     * @param string $price The price string, including the currency symbol (e.g., '£4.40').
     * @param int $smallestUnit The smallest unit of the currency (e.g., 100 for pennies, cents).
     * @throws InvalidArgumentException if the price format is invalid.
     */
    public function __construct(string $price, int $smallestUnit = 100)
    {
        // Validate that smallestUnit is a positive integer
        if ($smallestUnit <= 0) {
            throw new InvalidArgumentException("Smallest unit must be a positive integer.");
        }

        // Extract the currency symbol and value (e.g., '£4.40')
        preg_match('/([^\d]+)(\d+(\.\d+)?)/', $price, $matches);

        if (count($matches) !== 4) {
            throw new InvalidArgumentException("Invalid price format");
        }

        $currencySymbol = $matches[1]; // Currency symbol (e.g., '£', '$')
        $priceValue = $matches[2]; // The numeric part of the price (e.g., '4.40')

        // Ensure the currency symbol is valid
        $this->currencySymbol = CurrencySymbol::tryFrom($currencySymbol);
        if (!$this->currencySymbol) {
            throw new InvalidArgumentException("Invalid currency symbol: {$currencySymbol}. Valid symbols are: " . implode(', ', array_map(fn($symbol) => $symbol->value, CurrencySymbol::cases())));
        }

        // Convert the price to the smallest unit (e.g., pennies, cents)
        $this->smallestUnit = $smallestUnit;
        $this->smallestUnitValue = (int) round((float) $priceValue * $smallestUnit);
    }

    /**
     * Get the value of the currency in its smallest unit (e.g., pennies, cents, etc.).
     *
     * @return int The value in the smallest unit.
     */
    public function getSmallestUnitValue(): int
    {
        return $this->smallestUnitValue;
    }

    /**
     * Get the value of the currency in the original unit (e.g., £4.40).
     *
     * @return float The value in the original currency unit (e.g., pounds, dollars).
     */
    public function getCurrencyValue(): float
    {
        return $this->smallestUnitValue / $this->smallestUnit;
    }

    /**
     * Return the currency symbol (e.g., '£', '$', '€', '¥').
     *
     * @return string The currency symbol.
     */
    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol->value;
    }

    /**
     * Return a human-readable string representation of the currency (e.g., "£4.40").
     *
     * @return string The formatted string representation of the currency.
     */
    public function __toString(): string
    {
        return $this->currencySymbol->value . number_format($this->getCurrencyValue(), 2);
    }
}
