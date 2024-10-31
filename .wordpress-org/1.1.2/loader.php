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

function seoli_stylesheet() {
	wp_enqueue_style('seoli_main_style', plugins_url('/css/main.css', __FILE__), array(), rand(111, 9999), 'all');
	wp_enqueue_style('seoli_bootstrap_style', plugins_url('/css/bootstrap.min.css', __FILE__), array(), rand(111, 9999), 'all');
	wp_enqueue_script('seoli_bootstrap_script', plugins_url('/js/bootstrap.min.js', __FILE__), array(), rand(111, 9999), 'all');
}

add_action('admin_print_styles', 'seoli_stylesheet');