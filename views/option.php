<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<?php 

	$aggregated = quizu_aggregate_qr($quizu);

	$result_criteria_flag = get_post_meta($quizu->quizu_id, '_quizu_result_criteria_flag', true);
	$result_criteria_class = !empty($result_criteria_flag) && $result_criteria_flag != 'results_by_path' ? 'results_by_score' : '';

?>

<li class="option parent <?php echo $option['id'] == 'essay' ? 'essay_option' : 'not_essay' ?>" data-path="<?php echo esc_attr($path['id']) ?>" data-question="<?php echo esc_attr($question['id']) ?>" data-option="<?php echo esc_attr($option['id']); ?>">
	
	<input class="option_id" type="hidden" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][id]" value="<?php echo esc_attr($option['id']) ?>">

	<input class="option title" type="text" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][value]" value="<?php echo esc_attr($option['value']) ?>">

	<div class="score_container <?php echo $option['id'] == 'essay' ? 'hidden' : '' ?>">
		<label>
			<?php esc_html_e('Score', 'quizuint') ?>
			<input class="score" type="text" placeholder="0/100" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][score]" value="<?php echo !empty($option['score']) ? esc_attr($option['score']) : 0; ?>">
		</label>
	</div>
	
	<div class="buttons">

		<?php if ($option['id'] != 'essay'): ?>
			<button class="controller delete option delete_option" data-nonce="<?php echo esc_attr(wp_create_nonce( 'delete_option' )); ?>" data-command="delete_option">
				<i class="fa fa-remove"></i>
				<?php esc_html_e( 'Delete', 'quizuint' ); ?>
			</button>
		<?php endif ?>

		<select class="link main <?php echo $result_criteria_class ?>" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][link]">
			<option value=""><?php esc_html_e( 'Next Question', 'quizuint' ); ?></option>
			
			<?php foreach ($aggregated as $questionb) : /* Reversed for display*/?>
				<?php if ($option['link']['linkid'] == $questionb['id'] && $option['link']['linkid'] !== ''): ?>
					<option data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" data-result="<?php echo $questionb['result'] == 'true' ? 'true': 'false'; ?>" selected value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
				<?php elseif($question['id'] == $questionb['id']): ?>
					<option class="hidden" data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" data-result="<?php echo $questionb['result'] == 'true' ? 'true': 'false'; ?>" value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
				<?php elseif($question['id'] !== $questionb['id']): ?>
					<option data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" data-result="<?php echo $questionb['result'] == 'true' ? 'true': 'false'; ?>" value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
				<?php endif; ?>
			<?php endforeach; ?>

			<option class="finalize"  data-linkid="finalize" value="finalize" <?php echo $option['link']['linkid'] == 'finalize' ? 'selected' : '' ?>><?php esc_html_e( 'Finalize Quiz', 'quizuint' ); ?></option>

		</select>

	</div>

	<div class="image <?php echo !empty($option['img']['url']) ? 'uploaded' : ''; ?>">
			
			<?php wp_nonce_field( 'my_image_upload', 'my_image_upload_nonce' ); ?>


			<label for="option_image_<?php echo esc_attr($question['id']) . '_' . esc_attr($option['id'])  ?>">
				<i class="fa fa-upload"></i>
				<?php esc_html_e('Image', 'quizuint') ?>
			</label>
			<input id="option_image_<?php echo esc_attr($question['id']) . '_' . esc_attr($option['id'])  ?>" class="image upload option_file" data-flag="true" name="option_image_<?php echo esc_attr($question['id']) . '_' . esc_attr($option['id'])  ?>" type="file" style="display: none;" value="Upload" data-option="<?php echo esc_attr($option['id']) ?>" data-parent="<?php echo esc_attr($question['id']) ?>" data-command="upload_option_image" data-nonce="<?php echo esc_attr(wp_create_nonce( 'upload_option_image' )); ?>" />
			
			<?php if (!empty($option['img']['url'])): ?>
				<img class="uploaded_image option_image" src="<?php echo esc_attr($option['img']['url']) ?>" height="50" width="50">
			<?php endif ?>

			<button class="controller delete image image_delete delete_option_image" value="Del" data-img="<?php echo esc_attr($option['img']['id']) ?>" data-command="delete_option_image" data-nonce="<?php echo esc_attr(wp_create_nonce( 'delete_option_image' )); ?>">
				<i class="fa fa-remove"></i>
			</button>

			<input class="image_id option_image_id" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][img][id]" type="hidden" value="<?php echo esc_attr($option['img']['id']) ?>" />
			<input class="image_url option_image_url" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][img][url]" type="hidden" value="<?php echo esc_attr($option['img']['url']) ?>" />
			<input class="option_image_flag" type="hidden" name="option_image_<?php echo esc_attr($question['id']) . '_' . esc_attr($option['id']) ?>_manual_save_flag" value="true" />

	</div>

	<?php if ($option['id'] == 'essay'): ?>

		<br>
		<br>

		<p class="essay_label"><?php esc_html_e('Answers', 'quizuint'); echo ':' ?></p>

		<button class="controller new_essay" data-command="new_essay" data-nonce="<?php echo wp_create_nonce('new_essay') ?>">
			<i class="fa fa-plus"></i>
			<?php esc_html_e('Add New', 'quizuint') ?>
		</button>

		<div class="essay_options">
			<?php foreach ($option['essay_ops'] as $essay): ?>
				<?php include( plugin_dir_path( __DIR__ ) . 'views/essay.php'); ?>
			<?php endforeach ?>
		</div>

	<?php endif ?>
	
</li>