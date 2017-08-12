<?php 

defined('ABSPATH') or die("Cannot access pages directly.");

// ADMIN AJAX CONTROLLER----------------------------------------------------------------------------------------------------------------

function multipq_admin_ajax(){

	// VALIDATIONS----------------------------------------------------------------------------------------------------------------

	/*Check nonce*/
	check_ajax_referer((string)$_POST['command'], $_POST['_ajax_nonce']);

	if ( ! current_user_can( 'mpq_edit_quizzes' )) {
	    die();
	}

	/*Retrieve variables*/
	$stripedvars = $_POST;
	
	/*Sanitize*/
	array_walk_recursive($stripedvars, "multipq_sanitize_deep");
	
	/*Define a strict list of actions*/
	$commands_list = array(
		'new_path', 
		'delete_path', 
		'new_result', 
		'delete_result',
		'new_question',
		'update_login_flag',
		'update_show_scores_flag',
		'update_result_criteria_flag',
		'update_path',
		'update_question',
		'update_result',
		'delete_question',
		'new_option',
		'new_essay',
		'delete_option',
		'upload_option_image',
		'update_quiz_tc',
		'delete_option_image',
		'sort_questions'
	);

	// Check for strict actions
	if (isset($stripedvars['command']) && in_array($stripedvars['command'], $commands_list)) {
		$command = sanitize_text_field($stripedvars['command']);
	}else{/*If not in list, exit*/
		die();
	}

	// Retrieve obj containing options
	if (isset($stripedvars['options'])) {
		$options = $stripedvars['options'];
	}

	// Retrieve Quiz ID
	if (isset($stripedvars['mpq_id']) && is_numeric($stripedvars['mpq_id'])) {
		$mpq_id = sanitize_text_field($stripedvars['mpq_id']);
	}

	// Retrieve result ID
	if (isset($stripedvars['result_id']) && strlen($stripedvars['result_id']) == 13) {
		$result_id = sanitize_text_field($stripedvars['result_id']);
	}

	// Retrieve parent question / option
	if (isset($stripedvars['parent']) && strlen($stripedvars['parent']) == 13) {
		$parent = sanitize_text_field($stripedvars['parent']);
	}

	// Retrieve option
	if (isset($stripedvars['option']) && (strlen($stripedvars['option']) == 13 || $stripedvars['option'] == 'essay')) {
		$option = sanitize_text_field($stripedvars['option']);
	}

	// Retrieve option image ID
	if (isset($stripedvars['option_img']) && strlen($stripedvars['option_img']) == 3) {
		$option_img = sanitize_text_field($stripedvars['option_img']);
	}

	// Retrieve titles
	if (isset($stripedvars['title'])) {
		$title = sanitize_text_field($stripedvars['title']);
	}

	// Retrieve content
	if (isset($stripedvars['content'])) {
		$content = $stripedvars['content'];
	}

	// Retrieve titles
	if (isset($stripedvars['highest'])) {
		$highest = sanitize_text_field($stripedvars['highest']);
	}

	// Retrieve color
	if (isset($stripedvars['color'])) {
		$color = sanitize_text_field($stripedvars['color']);
	}

	// Retrieve path
	if (!isset($stripedvars['path']) || empty($stripedvars['path'])) {
		$path = uniqid();
	}else{
		if (strlen($stripedvars['path']) == 13 || $stripedvars['path'] == 'default') {
			$path = sanitize_text_field($stripedvars['path']);
		}
	}

	// Retrieve new order for sorting
	if (isset($stripedvars['new_order']) && is_array($stripedvars['new_order'])) {
		$new_order = $stripedvars['new_order'];
	}

	// Retrieve new sorted path
	if (isset($stripedvars['new_path']) && ($stripedvars['new_path'] == 'default' || strlen($stripedvars['new_path']) == 13)) {
		$new_path = sanitize_text_field($stripedvars['new_path']);
	}

	// Retrieve previous question
	if (isset($stripedvars['prev_quest']) && strlen($stripedvars['prev_quest']) == 13) {
		$prev_quest = sanitize_text_field($stripedvars['prev_quest']);
	}

	// Retrieve path before sorting
	if (isset($stripedvars['prev_path']) && ($stripedvars['new_path'] == 'default' || strlen($stripedvars['new_path']) == 13)) {
		$prev_path = sanitize_text_field($stripedvars['prev_path']);
	}

	// Retrieve result flag
	if (isset($stripedvars['is_result']) && filter_var($stripedvars['is_result'], FILTER_VALIDATE_BOOLEAN)) {
		$is_result = $stripedvars['is_result'];
	}

	// Retrieve result flag
	if (isset($stripedvars['score_min']) && is_numeric($stripedvars['score_min'])) {
		$score_min = $stripedvars['score_min'];
	}else{
		$score_min = '0';
	}

	// Retrieve result flag
	if (isset($stripedvars['score_max']) && is_numeric($stripedvars['score_max'])) {
		$score_max = $stripedvars['score_max'];
	}else{
		$score_max = '0';
	}

	// Non-save Item count
	if (isset($stripedvars['non_save_item_count']) && is_numeric($stripedvars['non_save_item_count'])) {
		$nonsic = $stripedvars['non_save_item_count'];
	}

	// Retrieve manual save flag
	if (
		isset($stripedvars['flag']) 
		&& filter_var($stripedvars['flag'], FILTER_VALIDATE_BOOLEAN)
		|| $stripedvars['flag'] == 'results_by_path'
		|| $stripedvars['flag'] == 'results_by_total'
		|| $stripedvars['flag'] == 'results_by_option'
		) 
	{
		$flag = $stripedvars['flag'];
	}else{
		$flag = 'false';
	}

	// Retrieve manual save flag
	if (isset($stripedvars['essay_flag']) && filter_var($stripedvars['essay_flag'], FILTER_VALIDATE_BOOLEAN)) 
	{
		$essay_flag = $stripedvars['essay_flag'];
	}else{
		$essay_flag = 'false';
	}

	// Retrieve manual save flag
	if (isset($stripedvars['multiple_flag']) && filter_var($stripedvars['multiple_flag'], FILTER_VALIDATE_BOOLEAN)) 
	{
		$multiple_flag = $stripedvars['multiple_flag'];
	}else{
		$multiple_flag = 'false';
	}


	// Retrieve option image file
	if (isset($_FILES['option_image_'. $option ]) && ($_FILES['option_image_'. $option ]['size'] > 0) && wp_check_filetype(basename($_FILES['option_image_'. $option ]['name'])) ) {
		$file = $_FILES['option_image_'. $option ];
	}elseif(isset($stripedvars['option_image_'. $option]) && ($stripedvars['option_image_'. $option ]['filesizeInBytes'] > 0) && wp_check_filetype(basename($stripedvars['option_image_'. $option ]['filename']))){
		$file = $stripedvars['option_image_'. $option ];
	}

	// Retrieve result image file
	if (isset($_FILES['result_image_'. $parent ]) && ($_FILES['result_image_'. $parent ]['size'] > 0) && wp_check_filetype(basename($_FILES['result_image_'. $parent ]['name'])) ) {
		$file = $_FILES['result_image_'. $parent ];
	}elseif(isset($stripedvars['result_image_'. $parent]) && ($stripedvars['result_image_'. $parent ]['filesizeInBytes'] > 0) && wp_check_filetype(basename($stripedvars['result_image_'. $parent ]['filename']))){
		$file = $stripedvars['result_image_'. $parent ];
	}

	$current_post_status = get_post_status($stripedvars['mpq_id']);

	$postarr = array(
		'ID' => $stripedvars['mpq_id'],
		'post_type' => 'mpq_quiz',
	);

	if ($current_post_status == false || $current_post_status == 'auto-draft') {
		wp_insert_post($postarr);
	}

	// Game on.

	$mpq = new multipq_admin_ajax_controller_model($mpq_id, $path);

	switch ($command) {

		case 'new_path':

			$mpq->new_path($nonsic);

			break;


		case 'delete_path':

			$mpq->delete_path();

			break;

		case 'new_result':

			$mpq->new_result($nonsic);

			break;

		case 'delete_result':

			$mpq->delete_result($parent);

			break;

		case 'new_question':

			$mpq->new_question($path, $nonsic);

			break;

		case 'update_login_flag':

			$mpq->update_login_flag($flag);

			break;

		case 'update_show_scores_flag':

			$mpq->update_show_scores_flag($flag);

			break;

		case 'update_result_criteria_flag':

			$mpq->update_result_criteria_flag($flag);

			break;

		case 'update_path':

			$mpq->update_path($path, $title, $color);

			break;

		case 'update_question':

			$mpq->update_question($parent, $title, $essay_flag, $multiple_flag, $options);

			break;

		case 'update_result':

			$mpq->update_result($parent, $title, $content, $highest, $score_min, $score_max);

			break;

		case 'delete_question':

			$mpq->delete_question($parent);

			break;

		case 'new_option':
			
			$mpq->new_option($parent, $nonsic);

			break;

		case 'new_essay':
			
			$mpq->new_essay($parent, $option, $nonsic);

			break;

		case 'delete_option':

			$mpq->delete_option($path, $parent, $option);

			break;

		case 'upload_option_image':

			$mpq->upload_option_image($parent, $option, $file, $flag);

			break;

		case 'upload_result_image':

			$mpq->upload_option_image($parent, NULL, $file, $flag, $is_result);

			break;

		case 'delete_option_image':

			$mpq->delete_option_image($parent, $option, $option_img);

			break;

		case 'delete_result_image':

			$mpq->delete_result_image($parent, $option_img);

			break;

		case 'sort_questions':

			$mpq->sort_questions($new_order, $new_path, $prev_quest, $prev_path);

			break;

		case 'update_quiz_tc':

			$mpq->update_quiz_tc($mpq_id, $title, $content);

			break;
		
		default:

			echo 'Please try again';

			break;
	}

	// Destroy
	unset($mpq);

	die();
}

// REGISTER AJAX CONTROLLERS----------------------------------------------------------------------------------------------------------------

add_action( 'wp_ajax_multipq_admin_ajax', 'multipq_admin_ajax' );