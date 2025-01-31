<?php

namespace App\Enums;

enum CurrencySymbol: string
{
    case GBP = '£';
    case USD = '$';
    case EUR = '€';
    case JPY = '¥';
    // This is already overkill but add more if needed ...
}
