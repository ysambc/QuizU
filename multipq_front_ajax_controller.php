<?php 

defined('ABSPATH') or die("Cannot access pages directly.");

// FRONT AJAX CONTROLLER----------------------------------------------------------------------------------------------------------------
// **NOTE TO SELF** REMEMBER TO CORRECT NONCE SECURITY AND ESCAPE ALL THE THINGS
// function multipq_front_ajax(){

function multipq_front_ajax(){

	check_ajax_referer($_POST['command'], $_POST['_ajax_nonce']);/*Check nonce, if failed, die()*/
	
	/*Retrieve variables*/
	$stripedvars = $_POST;
	
	/*Sanitize*/
	array_walk_recursive($stripedvars, "multipq_sanitize_deep");
	
	/*Initialize mpq obj*/
	$mpq = new multipq_front_ajax_controller_model($stripedvars['mpq_id']);

	$allowed_commands = array('first', 'email');

	if (isset($stripedvars['command']) && in_array($stripedvars['command'], $allowed_commands)) {
		$stripedvars['command'] = sanitize_text_field($stripedvars['command']);
	}else{
		$stripedvars['command'] = '';
	}

	if (isset($stripedvars['email'])) {
		$stripedvars['email'] = sanitize_email($stripedvars['email']);
	}else{
		$stripedvars['email'] = '';
	}

	if (isset($stripedvars['title'])) {
		$stripedvars['title'] = sanitize_text_field($stripedvars['title']);
	}else{
		$stripedvars['title'] = '';
	}

	if (isset($stripedvars['quiz'])) {
		$stripedvars['quiz'] = sanitize_text_field($stripedvars['quiz']);
	}else{
		$stripedvars['quiz'] = '';
	}

	if (isset($stripedvars['image'])) {
		$stripedvars['image'] = esc_url($stripedvars['image']);
	}else{
		$stripedvars['image'] = '';
	}

	if (isset($stripedvars['content'])) {
		$stripedvars['content'] = sanitize_text_field($stripedvars['content']);
	}else{
		$stripedvars['content'] = '';
	}

	
	/*Choose what to do*/
	switch ($stripedvars['command']) {
		
		/*Download quiz*/
		case 'first':

			$id = $mpq->mpq_id;
			$questions = $mpq->all_questions;
			$results = $mpq->all_results;

			foreach ($results as $result => $value_re) {
				$results[$result]['content'] = multipq_run_shortcodes(multipq_wp_kses($value_re['content']));
			}
			
			/*Pass Quiz object to front JS*/
			print json_encode(array('id' => $id, 'questions' => $questions, 'results' => $results, 'resultsCriteriaFlag' => esc_html(get_post_meta($id, '_mpq_result_criteria_flag', true)), 'showScoresFlag' => esc_html(get_post_meta($id, '_mpq_show_scores_flag', true))));

		break;

		/*Send email*/
		case 'email':
			
			$to = $stripedvars['email'];
			$title = $stripedvars['title'];
			$image = $stripedvars['image'];
			$content = multipq_wp_kses($stripedvars['content']);
			
			$subject = htmlspecialchars_decode(multipq_run_string_template(get_option('mpq_settings_email_subject'), $mpq->mpq_id));

			$email = multipq_get_file_output(plugin_dir_path( __DIR__ ) . 'includes/basic_email.php');

			function set_content_type( $content_type ) {
				return 'text/html';
			}

			// Function to change email address

			function wpb_sender_email( $sender_email ) {
			    $sender_email = sanitize_email(get_option('mpq_settings_email_address'));
			    return $sender_email;
			}

			// Function to change sender name
			function wpb_sender_name( $sender_name ) {
				$sender_name = htmlspecialchars_decode(get_option('mpq_settings_email_name'));
				return $sender_name;
			}

			// Hooking up our functions to WordPress filters 
			add_filter( 'wp_mail_content_type', 'set_content_type' );
			add_filter( 'wp_mail_from_name', 'wpb_sender_name' );
			add_filter( 'wp_mail_from', 'wpb_sender_email' );
		
			$status = wp_mail($to, $subject, $email);

			remove_filter( 'wp_mail_content_type', 'set_content_type' );
			remove_filter( 'wp_mail_from_name', 'wpb_sender_name' );
			remove_filter( 'wp_mail_from', 'wpb_sender_email' );

			print json_encode(array('success' => $status, 'image' => $image));

		break;
		
		default:
			die();
		break;
	}
	
	/*Destroy mpq object*/
	unset($mpq);

	die();
}

// REGISTER AJAX CONTROLLERS----------------------------------------------------------------------------------------------------------------

add_action( 'wp_ajax_multipq_front_ajax', 'multipq_front_ajax' );
add_action( 'wp_ajax_nopriv_multipq_front_ajax', 'multipq_front_ajax' );