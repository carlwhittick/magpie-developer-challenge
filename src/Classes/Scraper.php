<?php

namespace App\Classes;

use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;
use App\Classes\ScrapeHelper;
use App\Classes\FileSize;
use App\Classes\Product;

class Scraper
{
    private \Ds\Set $products;

    public function __construct(
        private string $baseUri
    ) {
        // Initialize the products collection
        $this->products = new \Ds\Set();
    }

    public function run(): void
    {
        // Fetch the first document
        $document = ScrapeHelper::fetchDocument($this->baseUri);

        // Determine the total number of pages for scraping
        $pageCount = $document->filter('#pages a')->count();

        // Loop through all pages and scrape products
        for ($page = 1; $page <= $pageCount; $page++) {
            // Fetch the current page document
            $document = ScrapeHelper::fetchDocument("{$this->baseUri}/?page={$page}");

            // Scrape products on the current page
            $this->scrapeProducts($document);
        }

        // Save scraped products as JSON
        $this->saveToJson();
    }

    /**
     * Scrape all products from the provided document.
     */
    private function scrapeProducts(Crawler $document): void
    {
        // Find all product elements
        $productNodes = $document->filter('#products .product');

        // Process each product node
        $productNodes->each(function (Crawler $node) {
            // Process each product variation within a product
            $node->filterXPath('//*[@data-colour]')->each(function (Crawler $productVariation) use ($node) {
                // Extract and collect product data
                $productData = $this->extractProductData($node, $productVariation);

                // Add the product to the collection
                $this->products->add(new Product(...$productData));
            });
        });
    }

    /**
     * Extract data for a single product from the node and variation.
     */
    private function extractProductData(Crawler $node, Crawler $productVariation): array
    {
        $productData = [
            'title' => $node->filter('.product-name')->text('No title found'),
            'price' => $this->extractPrice($node),
            'imageUrl' => $node->filter('img')->image()->getUri(),
            'capacityMB' => (new FileSize($node->filter('.product-capacity')->text('No capacity found')))->getMegabytes(),
            'colour' => strtolower($productVariation->attr('data-colour')),
            'availabilityText' => $this->extractAvailabilityText($node),
            'isAvailable' => $this->isProductAvailable($node),
            'shippingText' => '',
            'shippingDate' => '',
        ];

        // Extract shipping information
        $this->extractShippingInfo($node, $productData);

        return $productData;
    }

    /**
     * Extract product price from the node.
     */
    private function extractPrice(Crawler $node): float
    {
        return (float) preg_replace('/[^\d\.]+/', '', $node->filterXPath('//*[contains(text(), "£")]')->text('No price found'));
    }

    /**
     * Extract availability text from the product node.
     */
    private function extractAvailabilityText(Crawler $node): string
    {
        $availabilityText = $node->filterXPath('//*[contains(text(), "Availability:")]')->text();
        return substr($availabilityText, strlen('Availability: '));
    }

    /**
     * Determine if the product is available based on the availability text.
     */
    private function isProductAvailable(Crawler $node): bool
    {
        return $this->extractAvailabilityText($node) === 'In Stock';
    }

    /**
     * Extract shipping information from the product node.
     */
    private function extractShippingInfo(Crawler $node, array &$productData): void
    {
        // This regex matches various shipping and delivery-related phrases, including:
        // - "Free Delivery" or "Free Deliveries"
        // - "Delivery from" or "Delivery by"
        // - "Order within X hours and have it"
        // - "Available on"
        // - "Free Shipping"
        // - "Unavailable for delivery"
        //
        // It also optionally captures a date following these phrases, such as specific 
        // delivery dates or times (e.g., "2025-02-05" or "tomorrow").
        $shippingTextRegex = '/((?:(?:Free\s)?Deliver[y|s](?:\sfrom|\sby)?|Order within \d hours and have it|Available on|Free Shipping[\s]?|Unavailable for delivery\s?)(?:\s(.+))?)/';

        // Search for shipping text using regex
        $node->each(function (Crawler $subnode) use ($shippingTextRegex, &$productData) {
            if (preg_match($shippingTextRegex, $subnode->text(), $matches)) {
                $productData['shippingText'] = $matches[0];
                if (!empty($matches[1])) {
                    $productData['shippingDate'] = Carbon::createFromTimestamp(strtotime($matches[1]))->toDateString();
                }
            }
        });
    }

    /**
     * Save the scraped products to a JSON file.
     */
    private function saveToJson(): void
    {
        var_dump($this->products->toArray());
        // Save the products collection as a JSON file
        file_put_contents('output.json', json_encode($this->products->toArray()));
    }
}
