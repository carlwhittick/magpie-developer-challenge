<?php

namespace App\Classes;

use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;
use App\Classes\ScrapeHelper;
use App\Classes\FileSize;
use App\Classes\Product;
use App\Classes\Cost;
use App\Enums\CurrencySymbol;

class Scraper
{
    private \Ds\Set $products;

    public function __construct(
        private string $baseUri
    ) {
        // Initialize the products collection
        $this->products = new \Ds\Set();
    }

    /**
     * Run the scraper process.
     *
     * This method fetches the first page of the product listing, determines the total 
     * number of pages, and scrapes all pages sequentially. After scraping, it saves 
     * the results to a JSON file.
     *
     * @return void
     */
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
     *
     * This method extracts product nodes from the provided document and processes each 
     * product and its variations, adding them to the products collection.
     *
     * @param Crawler $document The DOM document representing the current product listing page.
     * @return void
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
     *
     * This method gathers all relevant data for a single product, including the title, price, 
     * image URL, capacity, availability, color, and shipping information.
     *
     * @param Crawler $node The DOM node for the product.
     * @param Crawler $productVariation The DOM node for the product variation.
     * @return array An associative array containing product data.
     */
    private function extractProductData(Crawler $node, Crawler $productVariation): array
    {
        $productData = [
            'title' => $node->filter('.product-name')->text('No title found'),
            'price' => $this->extractPrice($node),
            'imageUrl' => $node->filter('img')->image()->getUri(),
            'capacity' => new FileSize($node->filter('.product-capacity')->text('No capacity found')),
            'colour' => strtolower($productVariation->attr('data-colour')),
            'availabilityText' => $this->extractAvailabilityText($node),
            'isAvailable' => $this->isProductAvailable($node),
            'shippingText' => '',
            'shippingDate' => null,
        ];

        // Extract shipping information
        $this->extractShippingInfo($node, $productData);

        return $productData;
    }

    /**
     * Extract product price from the node.
     *
     * This method searches for the price within the product node and converts it into a `Cost` object.
     * It accounts for various currency symbols by checking against the `CurrencySymbol` enum.
     *
     * @param Crawler $node The DOM node for the product.
     * @return Cost The price of the product, stored in the smallest unit (e.g., pennies or cents).
     */
    private function extractPrice(Crawler $node): Cost
    {
        $path = '//*[';
        foreach (CurrencySymbol::cases() as $i => $currencySymbol) {
            if ($i > 0) {
                $path .= ' or ';
            }
            $path .= 'contains(text(), "' . $currencySymbol->value . '")';
        }
        $path .= ']';

        $price = $node->filterXPath($path)->text('No price found');

        return new Cost($price);
    }

    /**
     * Extract availability text from the product node.
     *
     * This method extracts the availability information (e.g., "In Stock", "Out of Stock") from the node.
     *
     * @param Crawler $node The DOM node for the product.
     * @return string The availability text for the product.
     */
    private function extractAvailabilityText(Crawler $node): string
    {
        $availabilityText = $node->filterXPath('//*[contains(text(), "Availability:")]')->text();
        return substr($availabilityText, strlen('Availability: '));
    }

    /**
     * Determine if the product is available based on the availability text. If the text doesn't match the 
     * expected "In Stock" text it is assumed to be out of stock.
     *
     * This method checks if the product is marked as "In Stock".
     *
     * @param Crawler $node The DOM node for the product.
     * @return bool True if the product is available, false otherwise.
     */
    private function isProductAvailable(Crawler $node): bool
    {
        $prefix = 'In Stock';
        return substr($this->extractAvailabilityText($node), 0, strlen($prefix)) === $prefix;
    }

    /**
     * Extract shipping information from the product node.
     *
     * This method uses a regular expression to find shipping-related information, such as free shipping,
     * delivery dates, and any other relevant shipping details for the product.
     *
     * @param Crawler $node The DOM node for the product.
     * @param array $productData The array to store the extracted shipping information.
     * @return void
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
        $shippingTextRegex = '/(?:(?:Free\s)?Deliver[y|s](?:\sfrom|\sby)?|Order within \d hours and have it|Available on|Free Shipping[\s]?|Unavailable for delivery\s?)(?:\s(.+))?/';

        // Search for shipping text using regex
        $node->each(function (Crawler $subnode) use ($shippingTextRegex, &$productData) {
            if (preg_match($shippingTextRegex, $subnode->text(), $matches)) {
                $productData['shippingText'] = $matches[0];
                if (!empty($matches[1])) {
                    $productData['shippingDate'] = Carbon::createFromTimestamp(strtotime($matches[1]));
                }
            }
        });
    }

    /**
     * Save the scraped products to a JSON file.
     *
     * This method serializes the collected product data and saves it to a JSON file for later use.
     *
     * @return void
     */
    private function saveToJson(): void
    {
        // Save the products collection as a JSON file
        file_put_contents('output.json', json_encode($this->products->toArray(), JSON_PRETTY_PRINT));
    }
}
