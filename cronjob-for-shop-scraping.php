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
    private $outside_shops_search = [
        's_ishopping' => 'www.ishopping.pk/search/?q',
        's_shophive' => 'www.shophive.com/catalogsearch/result/?q',
        's_daraz' => 'www.daraz.pk/catalog/?q',
        's_mega' => 'www.mega.pk/search/',
        // 's_homeshopping' => 'homeshopping.pk/',
        's_yayvo' => 'yayvo.com/search/result/?q',
        's_vmart' => 'www.vmart.pk/?subcats=Y&status=A',
        's_telemart' => 'www.telemart.pk/catalogsearch/result/?q',
        's_myshop' => 'myshop.pk/catalogsearch/result/?q',
    ];
    private $invalid_offers = [];

	public function __construct() {
        // Add ajax action for cronjob
        add_action( 'wp_ajax_cronjob_for_shop_scraping', array($this, 'cronjob_execution') );
        add_action( 'wp_ajax_nopriv_cronjob_for_scraping', array($this, 'cronjob_execution') );
	}

    /**
     * Get APS products from database
     * @return array
     */
    private function get_aps_products() {
        global $wpdb;

        $sql = "SELECT p.ID AS id, p.post_title AS title, m.`meta_value` AS offers
                FROM wp_posts AS p
                INNER JOIN wp_postmeta AS m ON m.`post_id`=p.`ID` AND m.`meta_key`='aps-product-offers' AND m.`meta_value`!=''
                WHERE p.`post_status`='publish' AND p.id IN (32935, 33835)";
        $products = $wpdb->get_results($sql);
        // var_dump($products);

        return $products;
    }

    private function scrape_data_from_url($pid, $ptitle, $offer) {
        $site = null;

        foreach ($this->outside_shops_search as $key => $shop_url) {
            if( strpos($offer['url'], $shop_url) !== false ) {
                $site = [ 'shop' => $key, 'url' => $offer['url'] ];
                break;
            }
        }

        if($site == null) {
            foreach ($this->outside_shops as $key => $shop_url) {
                if( strpos($offer['url'], $shop_url) !== false ) {
                    $site = ['shop' => $key, 'url' => $offer['url']];
                    break;
                }
            }
        }

        if($site != null) {
            var_dump($site);
        } else {
            $this->invalid_offers[] = ['pid' => $pid, 'ptitle' => $ptitle, 'offer_url' => $offer['url']];
        }
    }

    /**
     * Cronjob execution
     */
	public function cronjob_execution() {
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
        set_time_limit(3600);

	    echo '--- Started cronjob ---<br>';

        $products = $this->get_aps_products();
        foreach ($products as $product) {
            $offers = unserialize($product->offers);
            if( !count($offers) ) continue;
//            var_dump($offers);
            foreach ($offers as $offer) {
                $data = $this->scrape_data_from_url($product->id, $product->title, $offer);
            }
        }

        echo '<br>--- Ended cronjob ---';

        wp_die();
    }
}

$CronjobForShopScraping = new CronjobForShopScraping();
?>