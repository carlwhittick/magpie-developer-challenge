<?php

namespace App;

require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;
use Seld\JsonLint\Undefined;

class Scrape
{
    private \Ds\Set $products;

    public function __construct(
        private string $baseUri
    ) {
        $this->products = new \Ds\Set();
    }

    public function run(): void
    {
        $document = ScrapeHelper::fetchDocument($this->baseUri);

        $pageCount = $document->filter('#pages a')->count();

        for ($i = 1; $i <= $pageCount; $i++) {
            $document = ScrapeHelper::fetchDocument($this->baseUri . '/?page=' . $i);

            $this->scrapeProducts($document);
        }

        file_put_contents('output.json', json_encode($this->products));
    }

    private function convertToBytes(string $from): ?int
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($from, 0, -2);
        $suffix = strtoupper(substr($from, -2));

        //B or no suffix
        if (is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $from);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            return null;
        }

        return $number * (1000 ** $exponent);
    }

    private function scrapeProducts($document)
    {
        $products = $document->filter('#products .product');


        $products->each(function (Crawler $node) {
            $node->filterXPath('//*[@data-colour]')->each(function (Crawler $productVariation) use ($node) {
                $productData = [
                    "title" => $node->filter('.product-name')->text('No title found'),
                    "price" => (float) preg_replace('/[^\d\.]+/', '', $node->filterXPath('//*[contains(text(), "£")]')->text('No price found')),
                    "imageUrl" => $node->filter('img')->image()->getUri(),
                    "capacityMB" => 0,
                    "colour" => strtolower($productVariation->attr('data-colour')),
                    "availabilityText" => '',
                    "isAvailable" => '',
                    "shippingText" => '',
                    "shippingDate" => '',
                ];

                $rawCapacity = $node->filter('.product-capacity')->text();
                $productData['capacityMB'] = $this->convertToBytes($rawCapacity) / 1000 / 1000;


                $prefix = 'Availability: ';
                $productData['availabilityText'] = $node->filterXPath('//*[contains(text(), "Availability:")]')->text();
                if (substr($productData['availabilityText'], 0, strlen($prefix)) == $prefix) {
                    $productData['availabilityText'] = substr($productData['availabilityText'], strlen($prefix));
                }

                $productData['isAvailable'] = $productData['availabilityText'] === 'In Stock';

                $node->each(function (Crawler $subnode) use (&$productData) {
                    $shippingTextRegex = '/((?:(?:Free\s)?Deliver[y|s](?:\sfrom|\sby)?|Order within \d hours and have it|Available on|Free Shipping[\s]?|Unavailable for delivery\s?)(?:\s(.+))?)/';
                    $match = preg_match($shippingTextRegex, $subnode->text(), $matches);

                    if (!$match) {
                        return;
                    }

                    $productData['shippingText'] = $matches[0];
                    if (array_key_exists(1, $matches) && $matches[1]) {
                        $productData['shippingDate'] = Carbon::createFromTimestamp(strtotime($matches[1]))->toDateString();
                    }
                });

                $this->products->add(new Product(...$productData));
            });
        });

        var_dump(json_encode($this->products->toArray()));

        file_put_contents('output.json', json_encode($this->products->toArray()));
    }
}

$scrape = new Scrape('https://www.magpiehq.com/developer-challenge/smartphones');
$scrape->run();
