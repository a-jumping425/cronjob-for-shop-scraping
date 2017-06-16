<?php

/**
 * Scrape the product data from daraz shop
 */

class Scrape_daraz {
    public function get_data_in_product_page($url) {
        try {
//            echo '<br>get_data_in_search_page: ' . $url;

            $html = \simplehtmldom_1_5\file_get_html($url);
            $product = $html->find('div.details-footer', 0);

            $price = $product->find('span.price', 0)->find('span', 1)->plaintext;
            $price = floatval(preg_replace('/[^\d\.]+/', '', $price));

            $availability = "In stock";     // This store haven't stock status.

//            echo "<br>$price, $availability";

            return [$price, $availability];
        } catch (Exception $e) {
//            echo $e->getMessage();
            return 0;
        }
    }

}