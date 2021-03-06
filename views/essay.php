<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<?php 

	$result_criteria_flag = get_post_meta($quizu->quizu_id, '_quizu_result_criteria_flag', true);
	$result_criteria_class = !empty($result_criteria_flag) && $result_criteria_flag != 'results_by_path' ? 'results_by_score' : '';

?>

<div class="essay">
	<input type="hidden"value="<?php echo esc_attr($essay['id']) ?>" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay_ops][<?php echo esc_attr($essay['id']) ?>][id]">

	<input type="text" class="essay_answer" data-id="<?php echo esc_attr($essay['id']) ?>" value="<?php echo esc_attr($essay['value']) ?>" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay_ops][<?php echo esc_attr($essay['id']) ?>][value]">

	<div class="score_container">
		<label>
			<?php esc_html_e('Score', 'quizuint') ?>
			<input class="score" type="text" placeholder="0/100" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay_ops][<?php echo esc_attr($essay['id']) ?>][score]" value="<?php echo !empty($essay['score']) ? esc_attr($essay['score']) : 0; ?>">
		</label>
	</div>

	<select class="link second <?php echo $result_criteria_class ?>" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay_ops][<?php echo esc_attr($essay['id']) ?>][link]">
		<option value=""><?php esc_html_e( 'Next Question', 'quizuint' ); ?></option>
		
		<?php foreach ($aggregated as $questionb) : /* Reversed for display*/?>
			<?php if ($essay['link']['linkid'] == $questionb['id'] && !empty($essay['link']['linkid'])): ?>
				<option data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" data-result="<?php echo ($questionb['result'] == 'true' ? 'true': 'false'); ?>" selected value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
			<?php elseif($question['id'] == $questionb['id']): ?>
				<option class="hidden" data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" data-result="<?php echo ($questionb['result'] == 'true' ? 'true': 'false'); ?>" value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
			<?php elseif($question['id'] !== $questionb['id']): ?>
				<option data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" data-result="<?php echo ($questionb['result'] == 'true' ? 'true': 'false'); ?>" value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
			<?php endif; ?>
		<?php endforeach; ?>

		<option class="finalize"  data-linkid="finalize" value="finalize" <?php echo $option['link']['linkid'] == 'finalize' ? 'selected' : '' ?>><?php esc_html_e( 'Finalize Quiz', 'quizuint' ); ?></option>

	</select>

	<button class="controller remove_essay <?php echo count($option['essay_ops']) > 1 ? '' : 'hidden' ?>" data-id="<?php echo esc_attr($essay['id'])  ?>" data-command="delete_essay" data-nonce="<?php echo wp_create_nonce('new_essay') ?>">
		<i class="fa fa-remove"></i>
		<?php echo esc_html_e('Delete', 'quizuint') ?>
	</button>

</div>
