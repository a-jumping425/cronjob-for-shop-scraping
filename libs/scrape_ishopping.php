<?php

/**
 * Scrape the product data from ishopping shop
 */

class Scrape_ishopping {
    public function get_data_in_product_page($url) {
        try {
//            echo '<br>get_data_in_search_page: ' . $url;

            $html = \simplehtmldom_1_5\file_get_html($url);
            $right_side = $html->find('div.right-p-side', 0);

            $price = $right_side->find('span.price', 0)->plaintext;
            $price = floatval(preg_replace('/[^\d\.]+/', '', $price));

            $availability = $right_side->find('p.availability span.value', 0)->plaintext;

//            echo "<br>$price, $availability";

            return [$price, $availability];
        } catch (Exception $e) {
//            echo $e->getMessage();
            return 0;
        }
    }

}