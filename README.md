[![Built with Devbox](https://www.jetify.com/img/devbox/shield_galaxy.svg)](https://www.jetify.com/devbox/docs/contributor-quickstart/)

# Magpie PHP Developer Challenge

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Directory Structure](#directory-structure)
4. [Installation](#installation)
5. [Usage](#usage)
6. [Classes and Enums](#classes-and-enums)

---

## Overview

This repository contains my solution to the **Magpie PHP Developer Challenge**. The goal of my implementation is to create a resilient crawler/scraper that can adapt even when the styling of the product pages changes. To achieve this, I’ve minimized direct dependencies on styling classes, relying only on a few classes that are clearly not related to page styling. The remaining data is inferred based on the available content.

Although this implementation may seem more complex than required, it demonstrates my approach to problem-solving and designing flexible systems.

### Core Components of the Project:
- **CurrencySymbol Enum**: Defines supported currency symbols (e.g., GBP, USD, EUR) and maps them to their respective symbols.
- **Price Class**: Manages prices in a currency-agnostic manner, storing them in the smallest unit (e.g., pennies or cents).
- **FileSize Class**: Provides utilities for handling and converting file sizes in various units (e.g., bytes, KB, MB, GB).
- **Scraper Class**: Handles the page scraping and navigation. 

This project leverages PHP's **enum** functionality (introduced in PHP 8.1) to represent currencies and file size units.

---

## Features

- **Automated Pagination Handling**:
  - Automatically detects and scrapes all available pages of the product catalog.
  - Determines the total number of pages using the website’s pagination structure, ensuring complete data extraction across multiple pages.

- **Product Data Extraction**:
  - Extracts essential product details including:
    - **Title**: The product’s name or title.
    - **Price**: The product's price, parsed and stored in the smallest unit (e.g., pennies or cents).
    - **Image URL**: Retrieves the product image URL for reference.
    - **Capacity**: Uses the `FileSize` class to extract and manage product capacity (e.g., for storage devices).
    - **Color**: Extracts product color information from variations.
    - **Availability**: Determines product availability based on availability text, marking products as in stock or out of stock.
  
- **Shipping Information Extraction**:
  - Extracts shipping-related details using regular expressions to match phrases like "Free Shipping", "Order within X hours", "Available on", and "Unavailable for delivery".
  - Optionally captures dates (e.g., "2025-02-05" or "tomorrow") for delivery, using `Carbon` for date manipulation.

- **Currency-Agnostic Price Handling**:
  - Supports various currency symbols, including `£`, `$`, `€`, `¥`, and more.
  - Handles and stores product prices in the smallest unit (e.g., pennies or cents) for accurate calculations, using the `Cost` class and the `CurrencySymbol` enum.

- **Structured Data Output**:
  - Saves scraped product data in a structured JSON file (`output.json`), which includes detailed information such as price, availability, and shipping details.

- **Resilience to Layout Changes**:
  - Designed to work even if the website’s styling or layout changes, by focusing on data extraction rather than specific class-based or style-based targeting.

---

## Directory Structure

The project follows a modular structure to ensure clarity and maintainability:

```
/src
  /Classes
    FileSize.php         # Class for managing file size conversions
    Price.php            # Class for handling prices in various currencies
    ScrapeHelper.php     # Helper class for fetching documents
    Product.php          # Object for storing product information
  /Enums
    CurrencySymbol.php   # Enum for currency symbols (GBP, USD, EUR, etc.)
    FileSizeUnit.php     # Enum for file size units (B, KB, MB, GB, etc.)
  Scrape.php             # Main script for scraping product details
```

---

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/carlwhittick/magpie-developer-challenge.git
   ```

2. **Install dependencies with Composer:**
   ```bash
   composer install
   ```

3. **Ensure you have PHP 8.3+ and the PHP Data Structures extension installed.**

---

## Usage

To trigger the scraping and generate the `output.json` file, run:

```bash
php ./src/Scrape.php
```

---

## Classes and Enums

### `CurrencySymbol` Enum

The `CurrencySymbol` enum defines various supported currency symbols. It includes:
- `GBP` for the British Pound (£)
- `USD` for the United States Dollar ($)
- `EUR` for the Euro (€)
- `JPY` for the Japanese Yen (¥)

This enum allows you to handle and validate currency symbols consistently.

### `Price` Class

The `Price` class is designed to manage prices in a currency-agnostic manner. It accepts a price string (e.g., `'£4.40'`) or a numeric value and a currency symbol, and stores the price in the smallest unit (e.g., pennies or cents) for precise calculations.

Methods:
- `getAmount()`: Returns the price in the smallest unit (e.g., pennies or cents).
- `getCurrency()`: Returns the currency symbol (e.g., `CurrencySymbol::GBP`).

### `FileSize` Class

The `FileSize` class provides methods to convert file sizes between various units (e.g., bytes, KB, MB, GB). It supports the following methods:
- `getBytes()`: Returns the size in bytes.
- `getKilobytes()`: Returns the size in kilobytes (KB).
- `getMegabytes()`: Returns the size in megabytes (MB).
- `getGigabytes()`: Returns the size in gigabytes (GB).
- `getTerabytes()`: Returns the size in terabytes (TB).
- `getPetabytes()`: Returns the size in petabytes (PB).

---

## License

This project is open-source and available under the [MIT License](LICENSE).
