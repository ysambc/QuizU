<?php

defined('ABSPATH') or die("Cannot access pages directly.");

if (empty($linked_quiz)) {
	$linked_quiz = multipq_find_linked_quiz();
}

$mpq = new multipq_front_ajax_controller_model($linked_quiz, $path ="default");/*Create new object for use in widget*/

$result_criteria_flag = get_post_meta($linked_quiz, '_mpq_result_criteria_flag', true);

$mpq_login_required_flag = false;

if (  
		(
		  	get_option('mpq_settings_user_login_flag') == 'true'
		||  get_post_meta($linked_quiz, '_mpq_user_login_flag', true) == 'true'
		)

  		&&  !is_user_logged_in()
  ) 
{
  $mpq_login_required_flag = true;
}

if ((get_post_status($linked_quiz) == 'publish' || is_preview()) && !$mpq_login_required_flag && $linked_quiz != NULL): /*If NOT empty or = 0*/

if (empty($question) && !empty($mpq->all_questions['default']['questions'])) {
	$path = $mpq->all_questions['default'];
	$question = array_shift($path['questions']);
}

if (!empty($path['color'])) {
	$current_color =  $path['color'];
}else{
	$current_color =  get_option('mpq_settings_default_color');
}

?>

<div class="multipq_widget <?php echo $question['multiple_choice_flag'] == 'true' ? 'multiple_choice' : '' ; echo ' '; echo isset($mpq_in_sidebar) ? 'sidebar' : '' ?>" data-id="<?php echo $linked_quiz ?>" data-done="<?php echo wp_create_nonce( 'done' ) ?>" data-first="<?php echo wp_create_nonce( 'first' ) ?>" data-email="<?php echo wp_create_nonce( 'email' ) ?>">

	<i class="fa fa-spinner fa-spin"></i>

	<h3 class="quiz"><?php echo esc_html($mpq->mpq_title) ?></h3>

	<div class="content"><?php echo apply_filters('the_content', get_post($mpq->mpq_id)->post_content ); ?></div>
	
	<?php if (empty($question)): ?>
			
			<p>No questions found!</p>

	<?php else: ?>

	<p class="question"><?php echo esc_html($question['title']) ?></p>

		<?php foreach ($question['options'] as $option): ?>

			<?php if ( $question['essay_flag'] == 'true' && $option['id'] == 'essay'): ?>
				
				<input type="text" class="open_answer" placeholder="<?php echo esc_attr($option['value']) ?>">
				
				<div class="option_next" data-id="<?php echo esc_attr($option['id']) ?>" <?php echo (array_key_exists($option['link']['linkid'], $mpq->all_results) ? 'data-result="true"': 'data-result="false"'); ?> value="<?php echo esc_attr($option['value']) ?>" data-linkid="<?php echo esc_attr($option['link']['linkid']) ?>" data-linkpath="<?php echo esc_attr($option['link']['linkpath']) ?>">
					<p style="background-color: <?php echo esc_attr($current_color) ?>;"><?php esc_html_e('Continue', 'quizuint') ?></p>
					<?php if (!empty($option['img']['url'])): ?>
						<img class="option_image" src="<?php echo esc_attr($option['img']['url']) ?>" width="50" height="50">
					<?php endif ?>
				</div>

			<?php endif ?>

			<?php if($question['essay_flag'] != 'true' && $option['id'] != 'essay'): ?>

				<div class="option_next" data-id="<?php echo esc_attr($option['id']) ?>" <?php echo (array_key_exists($option['link']['linkid'], $mpq->all_results) ? 'data-result="true"': 'data-result="false"'); ?> value="<?php echo esc_attr($option['value']) ?>" data-linkid="<?php echo esc_attr($option['link']['linkid']) ?>" data-linkpath="<?php echo esc_attr($option['link']['linkpath']) ?>">
					<p style="background-color: <?php echo esc_attr($current_color) ?>;"><?php echo esc_html($option['value']) ?></p>
					<?php if (!empty($option['img']['url'])): ?>
						<img class="option_image" src="<?php echo esc_attr($option['img']['url']) ?>" width="50" height="50">
					<?php endif ?>
				</div>

			<?php endif ?>

		<?php endforeach ?>
	<?php endif; ?>

	<button class="next" style="background-color:<?php echo esc_attr($current_color) ?>;" data-nonce="<?php echo esc_attr(wp_create_nonce( 'next' )); ?>" data-path="<?php echo esc_attr($mpq->path) ?>" data-current="<?php echo !empty($question) ? esc_attr($question['id']) : '' ?>" data-command="next" data-path="<?php echo esc_attr($mpq->path) ?>" data-mpq="<?php echo esc_attr($mpq->mpq_id) ?>"><?php esc_html_e('Next', 'quizuint') ?></button>

</div>

<?php endif; ?>