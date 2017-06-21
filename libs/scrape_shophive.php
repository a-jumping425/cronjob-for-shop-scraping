<?php

/**
 * Scrape the product data from shophive shop
 */

class Scrape_shophive {
    public function get_curl_instance($url) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));

        return $curl;
    }

    public function get_data($html) {
        try {
            $html = \simplehtmldom_1_5\str_get_html($html);
            $product = $html->find('div.product-shop', 0);

            if( !$product )
                return 0;

            $min_price = 999999999999;
            foreach( $product->find('div.price-box span.price') as $span ) {
                $price = floatval(preg_replace('/[^\d\.]+/', '', $span->plaintext));
                if( $min_price > $price )
                    $min_price = $price;
            }

            $availability = trim( $product->find('div.sku span', 1)->plaintext );

            return [$min_price, $availability];
        } catch (Exception $e) {
            // echo $e->getMessage();
            return 0;
        }
    }
}