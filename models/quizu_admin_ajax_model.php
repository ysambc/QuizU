<?php

defined('ABSPATH') or die("Cannot access pages directly.");

/**
* This class is the Ajax Controller for the admin panel of quizu.
* This extends "quizu" base class and handles all save / update / delete 
* requests coming from the admin panel.
*/

class quizu_admin_ajax_controller_model extends quizu_main_model
{
	/**
	* This class extends the base class constructor and sets a new variable:
	* 'number_questions', which is used to determin generic names for new questions.
	*/

	public function __construct($quizu_id, $path = NULL){
			
		parent::__construct($quizu_id, $path);

		$this->number_path = count($this->all_questions);/*Count all paths*/
		
		if (!empty($path)) {
			$this->number_questions = count($this->all_questions[$path]['questions']);/*Count all questions in the parent path*/
		}

	}

	public function new_path($nonsic){/*Add a new path*/

		if(!empty($nonsic)){
			$this->number_path = $nonsic;
		}

		$path_id = uniqid();/*Create a random ID for this path*/
		$path_name = esc_html__('Branch ', 'quizuint') . ' # ' . strval(($this->number_path));/*Assign generic name to path*/

		if ($this->number_path == 0) {/*If there are no paths, create a default path with random ID*/
			$path_id = 'default';
			$this->all_questions['default'] = array('id' => 'default', 'name' => esc_html__('default branch', 'quizuint'), 'color' => esc_html(get_option('quizu_settings_default_color')), 'questions' => array());/*Insert new path into Quiz */
		}else{/*If there are, insert a new path in the array*/
			$this->all_questions[$path_id] = array('id' => $path_id, 'name' => $path_name, 'color' => esc_html(get_option('quizu_settings_default_color')), 'questions' => array());/*Insert new path into Quiz */
		}

		if (get_option('quizu_settings_autosave_flag') == 'true') {
			update_post_meta($this->quizu_id, $this->field, $this->all_questions);/*Update Quiz' questions custom field*/
		}

		$this->path = $path_id;

		$this->number_questions = count($this->all_questions[$this->path]['questions']);

		$path = $this->all_questions[$this->path];/*Retrieve the path recently inserted to pass it into the admin panel 'new path' view template*/
		$path['id'] = $this->path;

		$new_question_switch = true;

		include( plugin_dir_path(__DIR__) . 'views/path.php');/*Return new path template for admin panel*/
	}

	public function delete_path(){

		unset($this->all_questions[$this->path]);/*Delete the assigned path from the array, including all questions*/

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->quizu_id, $this->field, $this->all_questions);/*Update Quiz' questions custom field*/
		}

	}

	public function new_result($nonsic){/*Add a new path Quiz questions array*/

		$result_id = uniqid();
		$number_results = count($this->all_results);

		if(!empty($nonsic)){
			$number_results = $nonsic;
		}

		$result_title = esc_html__('Result', 'quizuint') . ' # ' . strval(($number_results+1));

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

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta($this->quizu_id, $this->Rfield, $this->all_results);/*Update Quiz*/
		}

		$quizu = $this;
		$result = $this->all_results[$result_id];

		include( plugin_dir_path(__DIR__) . 'views/result.php');/*Return new question template for admin panel*/

	}

	public function delete_result($result_id){

		unset($this->all_results[$result_id]);/*Unset question*/

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->quizu_id, $this->Rfield, $this->all_results);/*Update quiz*/
		}

	}

	public function new_question($path, $nonsic){/*Add a new question to current Quiz questions array*/

		if (!empty($path)) {
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
					'essay_ops' => array(array('id' => uniqid(), 'value' => esc_html__('Answer', 'quizuint') . ' # 1')),
				),
			),
			'result' => 'false',
			'flags' => array(
				'essay_flag' => 'false',
				'multiple_choice_flag' => 'false',
			),
		);

		$this->all_questions[$this->path]['questions'][$question['id']] = $question;/*Insert new question into Quiz' questions*/

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta($this->quizu_id, $this->field, $this->all_questions);/*Update Quiz*/
		}

		$quizu = $this;

		$path = $this->all_questions[$this->path];
		$path['id'] = $this->path;

		include( plugin_dir_path(__DIR__) . 'views/question.php');/*Return new question template for admin panel*/
	}

	public function update_login_flag($flag){
		update_post_meta( $this->quizu_id, '_quizu_user_login_flag', $flag );
	}

	public function update_show_scores_flag($flag){
		update_post_meta( $this->quizu_id, '_quizu_show_scores_flag', $flag );
	}
	
	public function update_result_criteria_flag($flag){
		update_post_meta( $this->quizu_id, '_quizu_result_criteria_flag', $flag );
	}

	public function update_path($path, $title, $color){/*Update existing question from admin panel input*/

		$this->all_questions[$path]['name'] = $title;/*Set modified question*/
		$this->all_questions[$path]['color'] = $color;/*Set modified question*/

		update_post_meta( $this->quizu_id, $this->field, $this->all_questions);/*Update question*/
	}

	public function update_question($parent, $title, $essay_flag, $multiple_flag, $options){

		/*Update existing question from admin panel input*/

		$question = $this->all_questions[$this->path]['questions'][$parent];/*Select question*/

		if (!empty($title)) {/*Check if title is not empty to avoid nameless questions*/
			$question['title'] = $title;
		}

		$question['flags']['essay_flag'] = $essay_flag;
		$question['flags']['multiple_choice_flag'] = $multiple_flag;

		/*MUST BE DEFINED INDIVIDUALLY*/
		foreach ($options as $option) {/*Reverse options array to match displayed items comming from AJAX and:*/
			$question['options'][$option['id']]['id'] = $option['id'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['value'] = $option['value'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['link']['linkid'] = $option['link']['linkid'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['link']['linkpath'] = $option['link']['linkpath'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['img']['id'] = $option['img']['id'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['img']['url'] = $option['img']['url'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['score'] = $option['score'];/*Define Quiz' Question's option value == input*/
			$question['options'][$option['id']]['essay_ops'] = $option['essay_ops'];/*Define Quiz' Question's option value == input*/
		}

		$this->all_questions[$this->path]['questions'][$parent] = $question;/*Set modified question*/

		update_post_meta( $this->quizu_id, $this->field, $this->all_questions);/*Update question*/
	}

	public function update_result($parent, $title, $content, $highest, $score_min, $score_max){/*Update existing result from admin panel input*/

		$this->all_results[$parent]['title'] = $title;
		$this->all_results[$parent]['content'] = $content;
		$this->all_results[$parent]['highest'] = $highest;
		$this->all_results[$parent]['score']['min'] = $score_min;
		$this->all_results[$parent]['score']['max'] = $score_max;

		update_post_meta( $this->quizu_id, $this->Rfield, $this->all_results);/*Update result*/
	}
	
	public function update_quiz_tc($quizu_id, $title, $content){/*Update existing result from admin panel input*/
		$postarr = array(
			'ID' => $quizu_id,
			'post_title' => $title,
			'post_content' => quizu_wp_kses($content),
			'post_type' => 'quizu_quiz',
		);

		wp_update_post( $postarr);
	}

	public function delete_question($parent){

		unset($this->all_questions[$this->path]['questions'][$parent]);/*Unset question*/

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->quizu_id, $this->field, $this->all_questions);/*Update quiz*/
		}

	}

	// Sort questions
	public function sort_questions($new_order, $new_path, $prev_quest, $prev_path){

		if (empty($new_path)) {
			$new_path = $this->path;
		}
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
			unset($prev_questions[$prev_quest]);

			// Remove old question from path
			$this->all_questions[$prev_path]['questions'] = $prev_questions;

		}

		var_dump($new_path);

		// Insert new question
		$this->all_questions[$new_path]['questions'] = array_merge($new_orderR, $new_questions);


		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->quizu_id, $this->field, $this->all_questions);
		}
	}
	

	public function new_option($parent, $nonsic){/*Add new option under current question*/

		$path = $this->all_questions[$this->path];
		$path['id'] = $this->path;

		$question = $path['questions'][$parent];/*Select question*/
		$question['id'] = $parent;

		$number_options = count($question['options']) -1;/*Count quesion's options*/

		if(!empty($nonsic)){
			$number_options = $nonsic - 1;
		}

		$option_id = uniqid();/*Define option ID*/

		$option_value = esc_html__('Option', 'quizuint') . ' # ' .($number_options + 1);/*Define default value*/

		$option = array(
			'id' => $option_id, 
			'value' => $option_value, 
			'link' => array(
				'linkid' => '', 
				'linkpath' => ''
				), 
			'img' => array(
				'id' => '',
				'url' => '',
			),
			'score' => 0,
		);

		$question['options'][$option['id']] = $option; /*Insert option*/

		$this->all_questions[$this->path]['questions'][$parent] = $question;/*Set modified question*/

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->quizu_id, $this->field, $this->all_questions);/*Update question*/
		}

		$quizu = $this;

		include( plugin_dir_path(__DIR__) . 'views/option.php');/*Return new option template*/
	}
	
	public function new_essay($parent, $option_id, $nonsic){

		$path = $this->all_questions[$this->path];
		$path['id'] = $this->path;

		$question = $this->all_questions[$path['id']]['questions'][$parent];
		$question['id'] = $parent;

		$option = $this->all_questions[$path['id']]['questions'][$parent]['options'][$option_id];
		$option['id'] = $option_id;

		$essay_id = uniqid();
		
		if(!empty($nonsic)){
			$essay_count = $nonsic;
		}else{
			$essay_count = count($option['essay_ops']);
		}
		
		$option['essay_ops'][$essay_id] = array('id' => $essay_id, 'value' => esc_html__('Answer', 'quizuint') . ' # ' . ($essay_count + 1));

		$this->all_questions[$path['id']]['questions'][$question['id']]['options'][$option['id']] = $option;

		$essay = $option['essay_ops'][$essay_id];
		$essay['id'] = $essay_id;

		$aggregated = quizu_aggregate_qr($this);

		$quizu = $this;

		include( plugin_dir_path( __DIR__ ) . 'views/essay.php');

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->quizu_id, $this->field, $this->all_questions);/*Update question*/
		}
	}

	public function delete_option($path, $parent, $option){

		unset($this->all_questions[$this->path]['questions'][$parent]['options'][$option]);/*Unset option*/

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta( $this->quizu_id, $this->field, $this->all_questions);/*Update question*/
		}
		
	}

	public function delete_option_image($parent, $option, $option_img){
		
		wp_delete_attachment($option_img);

		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['id'] = '';

		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['url'] = '';

		update_post_meta($this->quizu_id, $this->field, $this->all_questions);
	}

	public function delete_result_image($parent, $option_img){

		wp_delete_attachment($option_img);

		$this->all_results[$parent]['img']['id'] = '';

		$this->all_results[$parent]['img']['url'] = '';

		if (get_option('quizu_settings_autosave_quiz_flag') == 'true') {
			update_post_meta($this->quizu_id, $this->Rfield, $this->all_results);
		}
		
	}

	public function upload_option_image($parent, $option, $file, $flag, $is_result){

       if(isset($file) && $file['filesizeInBytes'] > 0) { // No file was passed
        	
        	if ($is_result) {
        		$this->all_results[$parent]['img']['id'] = $file['id'];
        		$this->all_results[$parent]['img']['url'] = $file['url'];
            	update_post_meta($this->quizu_id, $this->Rfield, $this->all_results);
        	}else{
         		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['id'] = $file['id'];
         		$this->all_questions[$this->path]['questions'][$parent]['options'][$option]['img']['url'] = $file['url'];
            	update_post_meta($this->quizu_id, $this->field, $this->all_questions);
        	}

       }

	}
}