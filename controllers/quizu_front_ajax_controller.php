<?php 

defined('ABSPATH') or die("Cannot access pages directly.");

// FRONT AJAX CONTROLLER----------------------------------------------------------------------------------------------------------------
// **NOTE TO SELF** REMEMBER TO CORRECT NONCE SECURITY AND ESCAPE ALL THE THINGS
// function quizu_front_ajax(){

function quizu_front_ajax(){

	check_ajax_referer($_POST['command'], $_POST['_ajax_nonce']);/*Check nonce, if failed, die()*/
	
	/*Retrieve variables*/
	$stripedvars = $_POST;
	
	/*Sanitize*/
	array_walk_recursive($stripedvars, "quizu_sanitize_deep");
	
	/*Initialize quizu obj*/
	$quizu = new quizu_front_ajax_controller_model($stripedvars['quizu_id']);

	$allowed_commands = array('email');

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

	if (isset($stripedvars['message'])) {
		$stripedvars['message'] = sanitize_textarea_field($stripedvars['message']);
	}else{
		$stripedvars['message'] = '';
	}

	if (isset($stripedvars['scores']) && is_array($stripedvars['scores'])) {
		$stripedvars['scores'] = $stripedvars['scores'];
	}else{
		$stripedvars['scores'] = array();
	}

	if (isset($stripedvars['content'])) {
		$stripedvars['content'] = sanitize_text_field($stripedvars['content']);
	}else{
		$stripedvars['content'] = '';
	}

	
	/*Choose what to do*/
	switch ($stripedvars['command']) {

		/*Send email*/
		case 'email':
			
			$to = $stripedvars['email'];
			$title = $stripedvars['title'];
			$content = quizu_wp_kses($stripedvars['content']);
			$message = quizu_wp_kses($stripedvars['message']);
			$subject = htmlspecialchars_decode(quizu_run_string_template(esc_html__(get_option('quizu_settings_email_subject'), $quizu->quizu_id)));

			$mail_parts['title'] = $title;
			$mail_parts['subject'] = $subject;
			$mail_parts['content'] = $content;
			$mail_parts['message'] = $message;
			$mail_parts['scores'] = $stripedvars['scores'];

			$email = quizu_get_file_output(plugin_dir_path( __DIR__ ) . 'includes/basic_email.php', $mail_parts);

			function set_content_type( $content_type ) {
				return 'text/html';
			}

			// Function to change email address

			function wpb_sender_email( $sender_email ) {
			    $sender_email = sanitize_email(get_option('quizu_settings_email_address'));
			    return $sender_email;
			}

			// Function to change sender name
			function wpb_sender_name( $sender_name ) {
				$sender_name = htmlspecialchars_decode(get_option('quizu_settings_email_name'));
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

			print json_encode(array('success' => $status, 'email' => $email));

		break;
		
		default:
			die();
		break;
	}
	
	/*Destroy quizu object*/
	unset($quizu);

	die();
}

// REGISTER AJAX CONTROLLERS----------------------------------------------------------------------------------------------------------------

add_action( 'wp_ajax_quizu_front_ajax', 'quizu_front_ajax' );
add_action( 'wp_ajax_nopriv_quizu_front_ajax', 'quizu_front_ajax' );