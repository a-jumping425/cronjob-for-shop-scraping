<?php

/**
 * Scrape the product data from daraz shop
 */

class Scrape_daraz {
    public function get_curl_instance($url) {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        return $curl;
    }

    public function get_data($html) {
        try {
            $html = \simplehtmldom_1_5\str_get_html($html);
            $product = $html->find('div.details-footer', 0);

            if( !$product )
                return 0;

            $price = $product->find('span.price', 0)->find('span', 1)->plaintext;
            $price = floatval(preg_replace('/[^\d\.]+/', '', $price));

            $availability = $product->find('div.actions span.label', 0)->plaintext;
            if( stripos($availability, 'buy now') !== false ) {
                $availability = "In stock";
            } else {
                $availability = "Out of stock";
            }

            return [$price, $availability];
        } catch (Exception $e) {
            // echo $e->getMessage();
            return 0;
        }
    }

}