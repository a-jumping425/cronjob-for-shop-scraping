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

        $sql = "SELECT p.id, p.post_title, m.`meta_value` AS offers
                FROM wp_posts AS p
                INNER JOIN wp_postmeta AS m ON m.`post_id`=p.`ID` AND m.`meta_key`='aps-product-offers' AND m.`meta_value`!=''
                WHERE p.`post_status`='publish' AND p.id IN (32935, 33835)";
        $products = $wpdb->get_results($sql);
        // var_dump($products);

        return $products;
    }

    /**
     * Cronjob execution
     */
	public function cronjob_execution() {
        // Set execution time
        set_time_limit(3600);

	    echo '--- Started cronjob ---<br>';

        $products = $this->get_aps_products();
        foreach ($products as $product) {
            $offers = unserialize($product->offers);
            if( !count($offers) ) continue;
            var_dump($offers);
        }

        echo '<br>--- Ended cronjob ---';

        wp_die();
    }
}

$CronjobForShopScraping = new CronjobForShopScraping();
?>