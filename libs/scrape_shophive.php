<?php

/**
 * Scrape the product data from shophive shop
 */

class Scrape_shophive {
    public function get_data_in_product_page($url) {
        try {
//            echo '<br>get_data_in_search_page: ' . $url;

            $html = \simplehtmldom_1_5\file_get_html($url);
            $product = $html->find('div.product-shop', 0);

            $min_price = 999999999999;
            foreach( $product->find('span.price') as $span ) {
                $price = floatval(preg_replace('/[^\d\.]+/', '', $span->plaintext));
                if( $min_price > $price )
                    $min_price = $price;
            }

            $availability = trim( $product->find('div.sku span', 1)->plaintext );

//            echo "<br>$price, $availability";

            return [$min_price, $availability];
        } catch (Exception $e) {
//            echo $e->getMessage();
            return 0;
        }
    }

    public function get_data_in_search_page($url) {
        try {
//            echo '<br>get_data_in_search_page: '. $url;

            $html = \simplehtmldom_1_5\file_get_html($url);

            $min_price = 999999999999;
            foreach( $html->find('ul.products-grid') as $product ) {
                foreach( $product->find('span.price') as $span ) {
                    $price = floatval(preg_replace('/[^\d\.]+/', '', $span->plaintext));
                    if( $min_price > $price )
                        $min_price = $price;
                }
            }

            $availability = "In stock";

            return [$min_price, $availability];
        } catch (Exception $e) {
//            echo $e->getMessage();
            return 0;
        }

        return 0;
    }
}