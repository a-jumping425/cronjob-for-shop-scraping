<?php

/**
 * Scrape the product data from homeshopping shop
 */

class Scrape_homeshopping {
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
            $product = $html->find('div#prodcut-desc', 0);

            if( !$product )
                return 0;

            $price = $product->find('em.ProductPrice', 0)->plaintext;
            $price = floatval(preg_replace('/[^\d\.]+/', '', $price));

            $availability = trim( $product->find('div.detlist2 span', 0)->plaintext );

            // echo "<br>$price, $availability";

            return [$price, $availability];
        } catch (Exception $e) {
            // echo $e->getMessage();
            return 0;
        }
    }

}