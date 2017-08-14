
<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<?php 
	
	$essay_flag = $question['flags']['essay_flag'];
	$multiple_choice_flag = $question['flags']['multiple_choice_flag'];

?>

<li class="question parent <?php echo $essay_flag == 'true' ? 'essay_type' : ''; echo ' '; echo $multiple_choice_flag == 'true' ? 'multiple_choice' : '' ?>" data-path="<?php echo esc_attr($path['id']) ?>" data-question="<?php echo esc_attr($question_id) ?>" data-sort="<?php echo esc_attr(wp_create_nonce('sort_questions')); ?>">
	
	<input class="question title" type="text" name="quizu_all_questions[<?php echo esc_attr($path_id) ?>][questions][<?php echo esc_attr($question['id']) ?>][title]" value="<?php echo esc_attr($question['title']) ?>" placeholder="">
	<input class="question id" type="hidden" name="quizu_all_questions[<?php echo esc_attr($path_id) ?>][questions][<?php echo esc_attr($question_id) ?>][id]" value="<?php echo esc_attr($question_id) ?>" placeholder="">

	<div class="buttons">
		<button class="controller delete delete_question" data-nonce="<?php echo esc_attr(wp_create_nonce( 'delete_question' )); ?>" data-command="delete_question">
			<i class="fa fa-remove"></i>
			<?php esc_html_e('Delete', 'quizuint') ?>
		</button>

		<button class="controller new new_option" data-nonce="<?php echo esc_attr(wp_create_nonce( 'new_option' )); ?>" data-command="new_option">
			<i class="fa fa-plus-circle"></i>
			<?php esc_html_e('New Option', 'quizuint') ?>
		</button>

		<button class="controller update update_question" data-nonce="<?php echo esc_attr(wp_create_nonce('update_question')); ?>" data-command="update_question">
			<i class="fa fa-save"></i>
			<?php esc_html_e('Save', 'quizuint') ?>
		</button>
	</div>

	<div class="essay_switch">
		<p class="label"><?php esc_html_e('Essay type?', 'quizuint') ?></p>
		<label class="switch">
		 	<input class="essay_question_flag controller flag" data-command="update_question" data-flag="<?php echo $question['flags']['essay_flag'] == 'true' ? 'false' : 'true' ; ?>" data-nonce="<?php echo wp_create_nonce('update_question') ?>" name="quizu_all_questions[<?php echo esc_attr($path_id) ?>][questions][<?php echo esc_attr($question_id) ?>][flags][essay_flag]" type="checkbox" <?php echo $question['flags']['essay_flag'] == 'true' ? 'checked' : '' ; ?>>
		 	<div class="slider round"></div>
		</label>
	</div>

	<i class="fa fa-compress collapse"></i>
	<i class="fa fa-arrows-alt sort_handle"></i>

	<ul class="options container">
		<?php

			if (!empty($question['options'])) {/*Display options*/
				$options = $question['options'];

				foreach ($options as $option_id => $option){
					include( plugin_dir_path( __DIR__ ) . 'views/option.php');
				}
			}
		 ?>
	</ul>

	<div class="multiple_choice_switch <?php echo $essay_flag == 'true' ? 'hidden' : '' ?>">
		<p class="label"><?php esc_html_e('Multiple choice?', 'quizuint') ?></p>
		<label class="switch">
		 	<input class="multiple_choice_flag controller flag" data-command="update_question" data-flag="<?php echo !empty($multiple_choice_flag) && $multiple_choice_flag == 'true' ? 'false' : 'true' ; ?>" data-nonce="<?php echo wp_create_nonce('update_question') ?>" name="quizu_all_questions[<?php echo esc_attr($path_id) ?>][questions][<?php echo esc_attr($question_id) ?>][flags][multiple_choice_flag]" type="checkbox" <?php echo !empty($multiple_choice_flag) && $multiple_choice_flag == 'true' ? 'checked' : '' ; ?>>
		 	<div class="slider round"></div>
		</label>
	</div>
</li>