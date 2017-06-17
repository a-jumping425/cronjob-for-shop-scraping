<?php
/**
 * Plugin Name: Cronjob For Shop Scraping
 * Plugin URI: http://www.wordpress.org/
 * Description: A plugin for the cronjob that scrapes the product data from other shops.
 * Version: 1.0
 * Author: Jumping
 * Author URI: http://www.wordpress.org/
 * License: GPL2
 */

class CronjobForShopScraping {
    private $outside_shops = [
        'ishopping' => 'www.ishopping.pk/',
        'shophive' => 'www.shophive.com/',
        'daraz' => 'www.daraz.pk/',
        'mega' => 'www.mega.pk/',
        'homeshopping' => 'homeshopping.pk/',
        'yayvo' => 'yayvo.com/',
        'vmart' => 'www.vmart.pk/',
        'telemart' => 'www.telemart.pk/',
        'myshop' => 'myshop.pk/',
    ];

    private $invalid_offers = [];

	public function __construct() {
        // Add ajax action for cronjob
        add_action( 'wp_ajax_cronjob_for_shop_scraping', array($this, 'cronjob_execution') );
        add_action( 'wp_ajax_nopriv_cronjob_for_shop_scraping', array($this, 'cronjob_execution') );
	}

    /**
     * Get APS products from database
     * @return array
     */
    private function get_aps_products() {
        global $wpdb;

        $sql = "SELECT p.ID AS id, p.post_title AS title, m.`meta_value` AS offers, m1.`meta_value` AS spec_general
                FROM wp_posts AS p
                INNER JOIN wp_postmeta AS m ON m.`post_id`=p.`ID` AND m.`meta_key`='aps-product-offers' AND m.`meta_value`!=''
                INNER JOIN wp_postmeta AS m1 ON m1.`post_id`=p.`ID` AND m1.`meta_key`='aps-attr-group-2129'
                WHERE p.`post_status`='publish' AND p.id IN (32935, 33835)";
        $products = $wpdb->get_results($sql);
//        var_dump($products);

        return $products;
    }

    private function scrape_data_from_url($offer) {
        $site = null;
        $product_data = null;

        foreach ($this->outside_shops as $key => $shop_url) {
            if( strpos($offer['url'], $shop_url) !== false ) {
                $site = ['shop' => $key, 'url' => $offer['url']];
                break;
            }
        }

        if($site != null) {
//            var_dump($site);
            switch ($site['shop']) {
                case 'ishopping':
                    $product_data = Scrape_ishopping::get_data_in_product_page($site['url']);
                    break;
                case 'shophive':
                    $product_data = Scrape_shophive::get_data_in_product_page($site['url']);
                    break;
                case 'daraz':
                    $product_data = Scrape_daraz::get_data_in_product_page($site['url']);
                    break;
                case 'mega':
                    $product_data = Scrape_mega::get_data_in_product_page($site['url']);
                    break;
                case 'homeshopping':
                    $product_data = Scrape_homeshopping::get_data_in_product_page($site['url']);
                    break;
                case 'yayvo':
                    $product_data = Scrape_yayvo::get_data_in_product_page($site['url']);
                    break;
                case 'vmart':
                    $product_data = Scrape_vmart::get_data_in_product_page($site['url']);
                    break;
                case 'telemart':
                    $product_data = Scrape_telemart::get_data_in_product_page($site['url']);
                    break;
                case 'myshop':
                    $product_data = Scrape_myshop::get_data_in_product_page($site['url']);
                    break;
            }
        }

        return $product_data;
    }

    private function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Cronjob execution
     */
	public function cronjob_execution() {
	    global $wpdb;

	    // Check IP for cronjob execution permission.
        /*
        $ip = $this->getRealIpAddr();
        if ($ip != '127.0.0.1') {    // Please add server ip that execute cronjob
            return;
        }
        */

        // Include libraries
        include_once (__DIR__ . '/libs/simple_html_dom.php');
        include_once (__DIR__ . '/libs/scrape_ishopping.php');
        include_once (__DIR__ . '/libs/scrape_shophive.php');
        include_once (__DIR__ . '/libs/scrape_daraz.php');
        include_once (__DIR__ . '/libs/scrape_mega.php');
        include_once (__DIR__ . '/libs/scrape_homeshopping.php');
        include_once (__DIR__ . '/libs/scrape_yayvo.php');
        include_once (__DIR__ . '/libs/scrape_vmart.php');
        include_once (__DIR__ . '/libs/scrape_telemart.php');
        include_once (__DIR__ . '/libs/scrape_myshop.php');

        // Set execution time
        set_time_limit(3600*10);

	    echo '--- Started cronjob ('. date('Y-m-d H:i:s') .') ---<br>';

        $products = $this->get_aps_products();
        foreach ($products as $product) {
            $offers = unserialize($product->offers);

            if( !count($offers) ) continue;

            $min_price = 999999999999;
            foreach ($offers as &$offer) {
                $data = $this->scrape_data_from_url($offer);
//                var_dump($data);

                if( $data ) {
                    if($min_price > $data[0]) {
                        $min_price = $data[0];
                    }

                    $offer['price'] = number_format($data[0]);
                    $offer['title'] = $data[1];
                } else {
                    if($min_price > $offer['price']) {
                        $min_price = $offer['price'];
                    }

                    $this->invalid_offers[] = ['pid' => $product->id, 'ptitle' => $product->title, 'offer_url' => $offer['url']];
                }
            }
            unset($offer);
//            var_dump($offers);

            // Update offers
            $wpdb->query( $wpdb->prepare("UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %d AND meta_key='aps-product-offers';", serialize($offers), $product->id) );
            // Update price
            $wpdb->query( $wpdb->prepare("UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %d AND meta_key='aps-product-price';", $min_price, $product->id) );

            $str = $product->title ." price in Pakistan is Rs. ". number_format($min_price) .". You can read price, specifications, latest reviews and rooting guide on TechJuice. The price was updated on ". date('dS F, Y') .".";
            // Update excerpt with new price
            $wpdb->query( $wpdb->prepare("UPDATE wp_posts SET post_excerpt = %s WHERE ID = %d", $str, $product->id) );
            // Update _yoast_wpseo_metadesc with new price
            $wpdb->query( $wpdb->prepare("UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %d AND meta_key='_yoast_wpseo_metadesc'", $str, $product->id) );
            // Update specifications - General
            $spec_general = unserialize($product->spec_general);
            $spec_general[2069] = number_format($min_price);
            $wpdb->query( $wpdb->prepare("UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %d AND meta_key='aps-attr-group-2129'", serialize($spec_general), $product->id) );
        }

        echo "<br>invalid_offers";
        var_dump($this->invalid_offers);

        // Send email with invalid offers to admin
        if( count($this->invalid_offers) ) {
            $to = get_option('admin_email', true);
            $subject = 'Cronjob report - Invalid offers';
            $message = '<p>Hi,</p><p>Please check invalid offers.</p>';
            foreach ($this->invalid_offers as $offer) {
                $message .= "<p>";
                $message .= "<strong>Product ID: </strong>" . $offer['pid'] . '<br>';
                $message .= "<strong>Product title: </strong>" . $offer['ptitle'] . '<br>';
                $message .= "<strong>Offer url: </strong>" . $offer['offer_url'];
                $message .= "</p>";
            }
            $message .= "<p>Thank you.</p>";
            $headers = array('From: Cronjob <cronjob@techjuice.pk>', 'Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);
        }

        echo '<br>--- Ended cronjob ('. date('Y-m-d H:i:s') .') ---';

        wp_die();
    }
}

$CronjobForShopScraping = new CronjobForShopScraping();
?>