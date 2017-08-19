<?php

defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * Plugin Name: QuizU
 * Plugin URI: https://github.com/ysambc/QuizU
 * Description: Create flexible branched / chained logic quizzes
 * Author: Jimmy Albert Bent Cano
 * Author URI: http://codebent.totalh.net
 * Copyright: Jimmy Albert Bent Cano
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain: quizuint
 * Version: 1.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * Brands, names, logs, images and other creative products are protected
 * by copyright laws and are not associated with this program's licence.
 *
 */

function quizu_init(){

	include( plugin_dir_path( __FILE__ ) . 'models/quizu_main_model.php');
	include( plugin_dir_path( __FILE__ ) . 'quizu_utils.php');
	include( plugin_dir_path( __FILE__ ) . 'quizu_setup.php');

	if (is_array(array_intersect_key(wp_get_current_user()->roles, get_option('quizu_settings_permissions')))) {
	  $current_user_is_capable = true;
	}else{
	  $current_user_is_capable = false;
	}

	if (is_admin() && $current_user_is_capable) {
		include( plugin_dir_path( __FILE__ ) . 'models/quizu_admin_ajax_model.php');
		include( plugin_dir_path( __FILE__ ) . 'controllers/quizu_admin_ajax_controller.php');
	}

	include( plugin_dir_path( __FILE__ ) . 'models/quizu_front_ajax_model.php');
	include( plugin_dir_path( __FILE__ ) . 'controllers/quizu_front_ajax_controller.php');
}

add_action('plugins_loaded', 'quizu_init' );