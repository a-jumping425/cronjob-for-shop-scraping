<?php

/**
 * Scrape the product data from myshop shop
 */

class Scrape_myshop {
    public function get_data_in_product_page($url) {
        try {
            // echo '<br>get_data_in_search_page: ' . $url;

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

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err || !$response) {
                // echo "cURL Error #:" . $err;
                return 0;
            }

            $html = \simplehtmldom_1_5\str_get_html($response);
            $product = $html->find('div.product-info-main', 0);

            if( !$product )
                return 0;

            $min_price = 999999999999;
            foreach( $product->find('span.price') as $span ) {
                $price = floatval(preg_replace('/[^\d\.]+/', '', $span->plaintext));
                if( $min_price > $price )
                    $min_price = $price;
            }

            $availability = "In stock";     // This shop haven't stock status

            // echo "<br>$price, $availability";

            return [$min_price, $availability];
        } catch (Exception $e) {
            // echo $e->getMessage();
            return 0;
        }
    }

}