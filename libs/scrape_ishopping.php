<?php

/**
 * Scrape the product data from ishopping shop
 */

class Scrape_ishopping {
    public function get_data_in_product_page($url) {
        try {
//            echo '<br>get_data_in_search_page: ' . $url;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
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

            if ($err) {
//                echo "cURL Error #:" . $err;
                return 0;
            }

            $html = \simplehtmldom_1_5\str_get_html($response);
            $product = $html->find('div.right-p-side', 0);

            if( !$product )
                return 0;

            $price = $product->find('span.price', 0)->plaintext;
            $price = floatval(preg_replace('/[^\d\.]+/', '', $price));

            $availability = $product->find('p.availability span.value', 0)->plaintext;

//            echo "<br>$price, $availability";

            return [$price, $availability];
        } catch (Exception $e) {
//            echo $e->getMessage();
            return 0;
        }
    }

    public function get_data_in_search_page($url) {
        try {
//            echo '<br>get_data_in_search_page: '. $url;

            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            $sword = urlencode( $query['q'] );

            $params = [
                "ticket" => "klevu-14920772243175751",
                "term" => $sword,
                "paginationStartsFrom" => "0",
                "sortPrice" => "true",
                "responseType" => "json",
                "resultForZero" => "1",
                "klevuShowOutOfStockProducts" => "false",
                "noOfResults" => "10"
            ];

            $param_str = "";
            foreach ($params as $key => $param) {
                if($param_str)
                    $param_str .= "&". $key ."=". $param;
                else
                    $param_str = $key ."=". $param;
            }

            $url = "https://eucs4.klevu.com/cloud-search/n-search/search?" . $param_str;
//            echo $url .'<br>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
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

            if ($err) {
//                echo "cURL Error #:" . $err;
            } else {
                $data = json_decode($response);
                if ($data->error->errorCode == "") {
                    $price = $data->result[0]->price;
                    if ($data->result[0]->inStock == "yes") {
                        $availability = "In stock";
                    } else {
                        $availability = "Out stock";
                    }

                    return [$price, $availability];
                }
            }
        } catch (Exception $e) {
//            echo $e->getMessage();
            return 0;
        }

        return 0;
    }
}