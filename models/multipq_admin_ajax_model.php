<?php

defined('ABSPATH') or die("Cannot access pages directly.");

/**
* This class is the Ajax Controller for the admin panel of multipq.
* This extends "multipq" base class and handles all save / update / delete 
* requests coming from the admin panel.
*/

class multipq_admin_ajax_controller_model extends multipq_main_model
{
	/**
	* This class extends the base class constructor and sets a new variable:
	* 'number_questions', which is used to determin generic names for new questions.
	*/

	public function __construct($mpq_id, $path = NULL){
			
		parent::__construct($mpq_id, $path);

		$this->number_path = count($this->all_questions);/*Count all paths*/
		
		if (isset($path)) {
			$this->number_questions = count($this->all_questions[$path]['questions']);/*Count all questions in the parent path*/
		}

	}

	public function new_path($nonsic){/*Add a new path*/

		$path_id = uniqid();/*Create a random ID for this path*/
		$path_name = esc_html__('Branch ', 'quizuint') . ' # ' . strval(($this->number_path));/*Assign generic name to path*/

		if(!empty($nonsic)){
			$this->number_path == $nonsic;
		}

		if ($this->number_path == 0) {/*If there are no paths, create a default path with random ID*/
			$path_id = 'default';
			$this->all_questions['default'] = array('id' => 'default', 'name' => esc_html__('default branch', 'quizuint'), 'color' => esc_html(get_option('mpq_settings_default_color')), 'questions' => array());/*Insert new path into Quiz */
		}else{/*If there are, insert a new path in the array*/
			$this->all_questions[$path_id] = array('id' => $path_id, 'name' => $path_name, 'color' => esc_html(get_option('mpq_settings_default_color')), 'questions' => array());/*Insert new path into Quiz */
		}

		if (get_option('mpq_settings_autosave_flag') == 'true') {
			update_post_meta($this->mpq_id, $this->field, $this->all_questions);/*Update Quiz' questions custom field*/
		}

		$this->path = $path_id;

		$this->number_questions = count($this->all_questions[$this->path]['questions']);

		$path = $this->all_questions[$this->path];/*Retrieve the path recently inserted to pass it into the admin panel 'new path' view template*/

		$new_question_switch = true;

		include( plugin_dir_path(__DIR__) . 'views/path.php');/*Return new path template for admin panel*/
	}

	public function delete_path(){

		unset($this->all_questions[$this->path]);/*Delete the assigned path from the array, including all questions*/

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->mpq_id, $this->field, $this->all_questions);/*Update Quiz' questions custom field*/
		}

	}

	public function new_result($nonsic){/*Add a new path Quiz questions array*/

		$result_id = uniqid();
		$number_results = count($this->all_results);
		$result_title = esc_html__('Result', 'quizuint') . ' # ' . strval(($number_results+1));

		if(!empty($nonsic)){
			$number_results = $nonsic;
		}

		$this->all_results[$result_id] = array(
			'id' => $result_id,
			'title' => $result_title,
			'content' => '',
			'highest' => '',
			'img' => array(
					'id' => '',
					'url' => ''
			),
			'score' => array(
					'min' => '',
					'max' => ''
			),
		 );/*Insert new result into Quiz */

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta($this->mpq_id, $this->Rfield, $this->all_results);/*Update Quiz*/
		}

		$mpq = $this;
		$result = $this->all_results[$result_id];

		include( plugin_dir_path(__DIR__) . 'views/result.php');/*Return new question template for admin panel*/

	}

	public function delete_result($result_id){

		unset($this->all_results[$result_id]);/*Unset question*/

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->mpq_id, $this->Rfield, $this->all_results);/*Update quiz*/
		}

	}

	public function new_question($path, $nonsic){/*Add a new question to current Quiz questions array*/

		if (isset($path)) {
			$this->path = $path;
		}

		if(!empty($nonsic)){
			$this->number_questions = $nonsic;
		}

		$question_id = uniqid();
		$option_id = uniqid();

		$question = array(/*Create new question array*/
			'id' => $question_id,/*Create unique ID for this question*/
			'title' => esc_html__('Question', 'quizuint'). ' # '.($this->number_questions +1),/*Define title*/
			'options' => array(/*Define options for this question*/
				$option_id => array(
					'id' => $option_id, 
					'value' => esc_html__('Option', 'quizuint') . ' # 1', 
					'link' => array(
						'linkid' => '', 
						'linkpath' => ''
						), 
					'img' => array(
						'id' => '',
						'url' => '',
					),
					'score' => 0,
				),
				'essay' => array(
					'id' => 'essay', 
					'value' => esc_html__('Open Answer', 'quizuint'), 
					'link' => array(
						'linkid' => '', 
						'linkpath' => ''
						), 
					'img' => array(
						'id' => '',
						'url' => '',
					),
					'score' => 0,
					'essay' => array(array('id' => uniqid(), 'value' => esc_html__('Answer', 'quizuint') . ' # 1')),
				),
			),
			'essay_flag' => 'false',
			'multiple_choice_flag' => 'false'
		);

		$this->all_questions[$this->path]['questions'][$question['id']] = $question;/*Insert new question into Quiz' questions*/

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta($this->mpq_id, $this->field, $this->all_questions);/*Update Quiz*/
		}

		$mpq = $this;

		$path = $this->all_questions[$this->path];
		$path_id = $path['id'];

		include( plugin_dir_path(__DIR__) . 'views/question.php');/*Return new question template for admin panel*/
	}

	public function update_login_flag($flag){
		update_post_meta( $this->mpq_id, '_mpq_user_login_flag', $flag );
	}

	public function update_show_scores_flag($flag){
		update_post_meta( $this->mpq_id, '_mpq_show_scores_flag', $flag );
	}
	
	public function update_result_criteria_flag($flag){
		update_post_meta( $this->mpq_id, '_mpq_result_criteria_flag', $flag );
	}

	public function update_path($path, $title, $color){/*Update existing question from admin panel input*/

		$this->all_questions[$path]['name'] = $title;/*Set modified question*/
		$this->all_questions[$path]['color'] = $color;/*Set modified question*/

		update_post_meta( $this->mpq_id, $this->field, $this->all_questions);/*Update question*/
	}

	public function update_question($parent, $title, $essay_flag, $multiple_flag, $options){

		/*Update existing question from admin panel input*/

		$question = $this->all_questions[$this->path]['questions'][$parent];/*Select question*/

		if (!empty($title)) {/*Check if title is not empty to avoid nameless questions*/
			$question['title'] = $title;
		}

		$question['essay_flag'] = $essay_flag;
		$question['multiple_choice_flag'] = $multiple_flag;

		/*MUST BE DEFINED INDIVIDUALLY*/
		foreach ($options as $option) {/*Reverse options array to match displayed items comming from AJAX and:*/
			$question['options'][$option['id']]['id'] = $option['id'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['value'] = $option['value'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['link']['linkid'] = $option['link']['linkid'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['link']['linkpath'] = $option['link']['linkpath'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['img']['id'] = $option['img']['id'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['img']['url'] = $option['img']['url'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['score'] = $option['score'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['essay'] = $option['essay'];/*Define Quiz' Question's option value == input*/
		}

		$this->all_questions[$this->path]['questions'][$parent] = $question;/*Set modified question*/

		update_post_meta( $this->mpq_id, $this->field, $this->all_questions);/*Update question*/
	}

	public function update_result($parent, $title, $content, $highest, $score_min, $score_max){/*Update existing result from admin panel input*/

		$this->all_results[$parent]['title'] = $title;
		$this->all_results[$parent]['content'] = $content;
		$this->all_results[$parent]['highest'] = $highest;
		$this->all_results[$parent]['score']['min'] = $score_min;
		$this->all_results[$parent]['score']['max'] = $score_max;

		update_post_meta( $this->mpq_id, $this->Rfield, $this->all_results);/*Update result*/
	}
	
	public function update_quiz_tc($mpq_id, $title, $content){/*Update existing result from admin panel input*/
		echo 'here';
		$postarr = array(
			'ID' => $mpq_id,
			'post_title' => $title,
			'post_content' => multipq_wp_kses($content),
		);

		wp_update_post( $postarr);
	}

	public function delete_question($parent){

		unset($this->all_questions[$this->path]['questions'][$parent]);/*Unset question*/

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->mpq_id, $this->field, $this->all_questions);/*Update quiz*/
		}

	}

	// Sort questions
	public function sort_questions($new_order, $new_path, $prev_quest, $prev_path){

		// Get new path questions
		$new_questions = $this->all_questions[$new_path]['questions'];

		$new_orderR = array_reverse($new_order);
		
		// Check if previous question and path are defined
		if (!empty($prev_quest) && !empty($prev_path)) {

			// Get previous path questions
			$prev_questions = $this->all_questions[$prev_path]['questions'];

			// Get previous question
			$prev_question = $prev_questions[$prev_quest];

			// Replace old question with new one
			$new_questions[$prev_quest] = $prev_question;

			// Unset previous question 
			foreach ($prev_questions as $question => $value) {
				if ($question == $prev_quest) {
					unset($prev_questions[$question]);
				}
			}

			// Remove old question from path
			$this->all_questions[$prev_path]['questions'] = $prev_questions;

		}

		// Insert new question
		$this->all_questions[$new_path]['questions'] = array_merge($new_orderR, $new_questions);

		// Update quiz

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->mpq_id, $this->field, $this->all_questions);
		}
	}
	

	public function new_option($parent, $nonsic){/*Add new option under current question*/

		$path = $this->all_questions[$this->path];

		$path_id = $path['id'];

		$question = $path['questions'][$parent];/*Select question*/

		$question_id = $parent;/*Select question*/

		$number_options = count($question['options']) -1;/*Count quesion's options*/

		if(!empty($nonsic)){
			$number_options = $nonsic - 1;
		}

		$option['id'] = uniqid();/*Define option ID*/

		$option_id = $option['id'];/*Define option ID*/

		$option['value'] = esc_html__('Option', 'quizuint') . ' # ' .($number_options + 1);/*Define default value*/

		$question['options'][$option['id']] = array(
			'id' => $option['id'], 
			'value' => $option['value'], 
			'link' => array(
				'linkid' => '', 
				'linkpath' => ''
				), 
			'img' => array(
				'id' => '',
				'url' => '',
			),
			'score' => 0,
		);/*Insert option*/

		$this->all_questions[$this->path]['questions'][$parent] = $question;/*Set modified question*/

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->mpq_id, $this->field, $this->all_questions);/*Update question*/
		}

		$mpq = $this;

		include( plugin_dir_path(__DIR__) . 'views/option.php');/*Return new option template*/
	}
	
	public function new_essay($parent, $option, $nonsic){

		$path = $this->all_questions[$this->path];

		$question = $this->all_questions[$path['id']]['questions'][$parent];

		$option = $this->all_questions[$path['id']]['questions'][$parent]['options'][$option];

		$essay_id = uniqid();
		
		if(!empty($nonsic)){
			$essay_count = $nonsic;
		}else{
			$essay_count = count($option['essay']);
		}
		
		$option['essay'][$essay_id] = array('id' => $essay_id, 'value' => esc_html__('Answer', 'quizuint') . ' # ' . ($essay_count + 1));

		$this->all_questions[$path['id']]['questions'][$parent]['options'][$option['id']] = $option;

		$value_es = $option['essay'][$essay_id];

		$aggregated = multipq_aggregate_qr($this);

		include( plugin_dir_path( __DIR__ ) . 'views/essay.php');

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->mpq_id, $this->field, $this->all_questions);/*Update question*/
		}
	}

	public function delete_option($path, $parent, $option){

		unset($this->all_questions[$this->path]['questions'][$parent]['options'][$option]);/*Unset option*/

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->mpq_id, $this->field, $this->all_questions);/*Update question*/
		}
		
	}

	public function delete_option_image($parent, $option, $option_img){
		
		wp_delete_attachment($option_img);

		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['id'] = '';

		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['url'] = '';

		update_post_meta($this->mpq_id, $this->field, $this->all_questions);
	}

	public function delete_result_image($parent, $option_img){

		wp_delete_attachment($option_img);

		$this->all_results[$parent]['img']['id'] = '';

		$this->all_results[$parent]['img']['url'] = '';

		if (get_option('mpq_settings_autosave_quiz_flag') == 'true') {
			update_post_meta($this->mpq_id, $this->Rfield, $this->all_results);
		}
		
	}

	public function upload_option_image($parent, $option, $file, $flag, $is_result){
		// Get the post type. Since this function will run for ALL post saves (no matter what post type), we need to know this.
		    // It's also important to note that the save_post action can runs multiple times on every post save, so you need to check and make sure the
		    // post type in the passed object isn't "revision"

		    $post_type = 'mpq_quiz';

		    // Make sure our flag is in there, otherwise it's an autosave and we should bail.
		    if(isset($flag)) { 

		        // Logic to handle specific post types
		        switch($post_type) {

		            // If this is a post. You can change this case to reflect your custom post slug
		            case 'mpq_quiz':

                            	print(json_encode(array('1')));
		                // HANDLE THE FILE UPLOAD

		                // If the upload field has a file in it
		               if(isset($file) && $file['size'] > 0) {
		               
                            	print(json_encode(array('2')));
		                    // Get the type of the uploaded file. This is returned as "type/extension"
		                    $arr_file_type = wp_check_filetype(basename($file['name']));
		                    $uploaded_file_type = $arr_file_type['type'];

		                    // Set an array containing a list of acceptable formats
		                    $allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png');

		                    // If the uploaded file is the right format
		                    if(in_array($uploaded_file_type, $allowed_file_types)) {

		                        // Options array for the wp_handle_upload function. 'test_upload' => false
		                        $upload_overrides = array( 'test_form' => false ); 

		                        // Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
		                        $uploaded_file = wp_handle_upload($file, $upload_overrides);

		                        // If the wp_handle_upload call returned a local path for the image
		                        if(isset($uploaded_file['file'])) {

		                            // The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
		                            $file_name_and_location = $uploaded_file['file'];

		                            // Generate a title for the image that'll be used in the media library
		                            if ($is_result) {
		                            	$file_title_for_media_library = 'Result image';
		                            }else{
		                            	$file_title_for_media_library = 'Option image';
		                            }

		                            // Set up options array to add this file as an attachment
		                            $attachment = array(
		                                'post_mime_type' => $uploaded_file_type,
		                                'post_title' => 'Uploaded image ' . addslashes($file_title_for_media_library),
		                                'post_content' => '',
		                                'post_status' => 'inherit'
		                            );

		                            // Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
		                            $attach_id = wp_insert_attachment( $attachment, $file_name_and_location );
		                            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		                            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
		                            wp_update_attachment_metadata($attach_id,  $attach_data);

		                            // Before we update the post meta, trash any previously uploaded image for this post.
		                           	
		                           	// You might not want this behavior, depending on how you're using the uploaded images.

		                            $existing_uploaded_image = (int) $this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['id'];

		                            if(is_numeric($existing_uploaded_image)) {
		                                wp_delete_attachment($existing_uploaded_image);
		                            }

	                            	$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['id'] = $attach_id;
	                            	$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['url'] = wp_get_attachment_url($attach_id);

		                            update_post_meta($this->mpq_id, $this->field, $this->all_questions);

		                            // Set the feedback flag to false, since the upload was successful
		                            $upload_feedback = false;


		                        } else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.
		                            $upload_feedback = 'There was a problem with your upload.';
		                            update_post_meta($post_id,$option.'_attached_image',$attach_id);

		                        }

		                    } else { // wrong file type
		                        $upload_feedback = 'Please upload only image files (jpg, gif or png).';
		                        update_post_meta($post_id,$option.'_attached_image',$attach_id);
		                    }

		                } elseif(isset($file) && $file['filesizeInBytes'] > 0) { // No file was passed
                        	if ($is_result) {
                        		$this->all_results[$parent]['img']['id'] = $file['id'];
                        		$this->all_results[$parent]['img']['url'] = $file['url'];
                            	update_post_meta($this->mpq_id, $this->Rfield, $this->all_results);
                            	print(json_encode(array('ues')));
                        	}else{
                         		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['id'] = $file['id'];
                         		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['url'] = $file['url'];
                            	update_post_meta($this->mpq_id, $this->field, $this->all_questions);
                            	print(json_encode(array($parent)));
                        	}

		                }else{
		                    $upload_feedback = false;
		                }

		                // Update the post meta with any feedback
		                update_post_meta($post_id,$option.'_attached_image_upload_feedback',$upload_feedback);

		            break;

		            default:
		        } // End switch

		    return;

		} // End if manual save flag
	}
}