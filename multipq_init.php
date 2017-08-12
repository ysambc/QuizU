<?php

defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * Plugin Name: QuizU
 * Plugin URI:  https://developer.wordpress.org/plugins/quizu/
 * Description: Create flexible branched / chained logic quizzes
 * Author: Jimmy Albert Bent Cano
 * Copyright: Jimmy Albert Bent Cano
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain: quizuint
 * Version: 1.0
 */

function multipq_init(){

	include( plugin_dir_path( __FILE__ ) . 'multipq_utils.php');
	include( plugin_dir_path( __FILE__ ) . 'multipq_setup.php');
	include( plugin_dir_path( __FILE__ ) . 'models/multipq_main_model.php');

	if (is_array(array_intersect_key(wp_get_current_user()->roles, get_option('mpq_settings_permissions')))) {
	  $current_user_is_capable = true;
	}else{
	  $current_user_is_capable = false;
	}

	if (is_admin() && $current_user_is_capable) {
		include( plugin_dir_path( __FILE__ ) . 'models/multipq_admin_ajax_model.php');
		include( plugin_dir_path( __FILE__ ) . 'controllers/multipq_admin_ajax_controller.php');
	}

	/*CANT !is_admin() ON FRONTEND COMPONENTS. AJAX IS ALWAYS ADMIN.*/

	include( plugin_dir_path( __FILE__ ) . 'models/multipq_front_ajax_model.php');
	include( plugin_dir_path( __FILE__ ) . 'controllers/multipq_front_ajax_controller.php');
}

add_action('plugins_loaded', 'multipq_init' );