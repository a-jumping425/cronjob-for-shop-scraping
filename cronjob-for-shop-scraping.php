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
     * Cronjob execution
     */
	public function cronjob_execution() {
        // Set execution time
        set_time_limit(3600);

	    echo '--- Started cronjob ---<br>';


        echo '<br>--- Ended cronjob ---';

        wp_die();
    }
}

$CronjobForShopScraping = new CronjobForShopScraping();
?>