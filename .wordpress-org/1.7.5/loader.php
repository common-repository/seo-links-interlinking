<?php
/**
 * Loads the plugin files
 *
 * @since 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register your stylesheet.
 */
function seoli_loader_admin_init() {
    wp_register_style('seoli_main_style', plugins_url('css/main.css',__FILE__ ), array(), SEOLI_VERSION);
    wp_register_style('seoli_bootstrap_style', plugins_url('css/bootstrap.min.css',__FILE__ ), array(), SEOLI_VERSION);
    wp_register_script( 'seoli_bootstrap_script', plugins_url('js/bootstrap.min.js',__FILE__ ), array(), SEOLI_VERSION);
}

/**
 * Enqueue our stylesheet.
 */
function seoli_stylesheet() {
    wp_enqueue_style('seoli_main_style');
    wp_enqueue_style('seoli_bootstrap_style');
    wp_enqueue_script('seoli_bootstrap_script');
}