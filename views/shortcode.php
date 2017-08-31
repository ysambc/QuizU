<?php

defined('ABSPATH') or die("Cannot access pages directly.");

if (empty($linked_quiz)) {
	$linked_quiz = quizu_find_linked_quiz();
}

$quizu = new quizu_front_ajax_controller_model($linked_quiz, $path ="default");/*Create new object for use in widget*/

$result_criteria_flag = get_post_meta($linked_quiz, '_quizu_result_criteria_flag', true);

$login_restriction_flag = false;

$texts_next = quizu_run_string_template(get_option('quizu_settings_texts_next'));

if (  
		(
		  	get_option('quizu_settings_user_login_flag') == 'true'
		||  get_post_meta($linked_quiz, '_quizu_user_login_flag', true) == 'true'
		)

  		&&  !is_user_logged_in()
  ) 
{
  $login_restriction_flag = true;
}

if ((get_post_status($linked_quiz) == 'publish' || is_preview()) && !$login_restriction_flag && $linked_quiz != NULL): /*If NOT empty or = 0*/

	if (empty($question) && !empty($quizu->all_questions['default']['questions'])) {
		$path = $quizu->all_questions['default'];
		$question = array_shift($path['questions']);
	}

	if (!empty($path['color'])) {
		$current_color =  $path['color'];
	}else{
		$current_color =  get_option('quizu_settings_default_color');
	}

	$multiple_class = $question['flags']['multiple_choice_flag'] == 'true' ? 'multiple_choice' : '' ;
	$essay_class = $question['flags']['essay_flag'] == 'true' ? 'essay_type' : '';
	$sidebar_class = !empty($quizu_in_sidebar) ? 'sidebar' : '';

?>

	<div class="quizu_widget <?php echo  $multiple_class . ' ' . $essay_class . ' ' . $sidebar_class ?>" data-id="<?php echo $linked_quiz ?>" data-done="<?php echo wp_create_nonce( 'done' ) ?>" data-first="<?php echo wp_create_nonce( 'first' ) ?>" data-email="<?php echo wp_create_nonce( 'email' ) ?>">
		<i class="fa fa-spinner fa-spin"></i>

		<h3 class="quiz"><?php echo esc_html($quizu->quizu_title) ?></h3>

		<div class="content"><?php echo apply_filters('the_content', get_post($quizu->quizu_id)->post_content ); ?></div>
		
		<?php if (empty($question)): ?>
				
				<p>No questions found!</p>

		<?php else: ?>

		<p class="question"><?php echo nl2br(esc_html($question['title'])) ?></p>

			<?php foreach ($question['options'] as $option): ?>

				<?php if ( $question['flags']['essay_flag'] == 'true' && $option['id'] == 'essay'): ?>
					
					<input type="text" class="open_answer" placeholder="<?php echo esc_attr($option['value']) ?>">
					
					<div class="option_next" data-id="<?php echo esc_attr($option['id']) ?>" <?php echo (array_key_exists($option['link']['linkid'], $quizu->all_results) ? 'data-result="true"': 'data-result="false"'); ?> value="<?php echo esc_attr($option['value']) ?>" data-linkid="<?php echo esc_attr($option['link']['linkid']) ?>" data-linkpath="<?php echo esc_attr($option['link']['linkpath']) ?>">
						<p style="background-color: <?php echo esc_attr($current_color) ?>;"><?php echo esc_html__($texts_next, 'quizuint') ?></p>
						<?php if (!empty($option['img']['url'])): ?>
							<img class="option_image" src="<?php echo esc_attr($option['img']['url']) ?>" width="50" height="50">
						<?php endif ?>
					</div>

				<?php endif ?>

				<?php if($question['flags']['essay_flag'] != 'true' && $option['id'] != 'essay'): ?>

					<div class="option_next" data-id="<?php echo esc_attr($option['id']) ?>" <?php echo (array_key_exists($option['link']['linkid'], $quizu->all_results) ? 'data-result="true"': 'data-result="false"'); ?> value="<?php echo esc_attr($option['value']) ?>" data-linkid="<?php echo esc_attr($option['link']['linkid']) ?>" data-linkpath="<?php echo esc_attr($option['link']['linkpath']) ?>">
						<p style="background-color: <?php echo esc_attr($current_color) ?>;"><?php echo esc_html($option['value']) ?></p>
						<?php if (!empty($option['img']['url'])): ?>
							<img class="option_image" src="<?php echo esc_attr($option['img']['url']) ?>" width="50" height="50">
						<?php endif ?>
					</div>

				<?php endif ?>

			<?php endforeach ?>
		<?php endif; ?>

		<button class="next" style="background-color:<?php echo esc_attr($current_color) ?>;" data-nonce="<?php echo esc_attr(wp_create_nonce( 'next' )); ?>" data-path="<?php echo esc_attr($quizu->path) ?>" data-current="<?php echo !empty($question) ? esc_attr($question['id']) : '' ?>" data-command="next" data-path="<?php echo esc_attr($quizu->path) ?>" data-quizu="<?php echo esc_attr($quizu->quizu_id) ?>"><?php echo esc_html__($texts_next, 'quizuint') ?></button>

	</div>

<?php endif; ?>