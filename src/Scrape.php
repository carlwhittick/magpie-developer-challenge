<?php

namespace App;

require 'vendor/autoload.php';

use App\Classes\Scraper;

// Instantiate and run the scraper
$scraper = new Scraper('https://www.magpiehq.com/developer-challenge/smartphones');
$scraper->run();
