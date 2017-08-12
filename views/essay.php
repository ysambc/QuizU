<div class="essay">
	<input type="hidden"value="<?php echo esc_attr($value_es['id']) ?>" name="mpq_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay][<?php echo esc_attr($value_es['id']) ?>][id]">

	<input type="text" class="essay_answer" data-id="<?php echo esc_attr($value_es['id']) ?>" value="<?php echo esc_attr($value_es['value']) ?>" name="mpq_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay][<?php echo esc_attr($value_es['id']) ?>][value]">

	<div class="score_container">
		<label>
			<?php esc_html_e('Score', 'quizuint') ?>
			<input class="score" type="text" placeholder="0/100" name="mpq_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay][<?php echo esc_attr($value_es['id']) ?>][score]" value="<?php echo !empty($value_es['score']) ? esc_attr($value_es['score']) : 0; ?>">
		</label>
	</div>

	<select class="link second <?php echo $mpq_result_criteria_flag != 'results_by_path' && !empty($mpq_result_criteria_flag) ? 'results_by_score' : '' ?>" name="mpq_all_questions[<?php echo esc_attr($path['id']) ?>][questions][<?php echo esc_attr($question['id']) ?>][options][<?php echo esc_attr($option['id']) ?>][essay][<?php echo esc_attr($value_es['id']) ?>][link]">
		<option value=""><?php esc_html_e( 'Next Question', 'quizuint' ); ?></option>
		
		<?php foreach ($aggregated as $questionb) : /* Reversed for display*/?>
			<?php if ($value_es['link']['linkid'] == $questionb['id'] && $value_es['link']['linkid'] !== ''): ?>
				<option data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" <?php echo (!empty($questionb['result']) && $questionb['result'] == 'true' ? 'data-result="true"': 'data-result="false"'); ?> selected value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
			<?php elseif($question['id'] == $questionb['id']): ?>
				<option class="hidden" data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" <?php echo (!empty($questionb['result']) && $questionb['result'] == 'true' ? 'data-result="true"': 'data-result="false"'); ?> value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
			<?php elseif($question['id'] !== $questionb['id']): ?>
				<option data-linkpath="<?php echo esc_attr($questionb['path']) ?>" data-linkid="<?php echo esc_attr($questionb['id']) ?>" <?php echo (!empty($questionb['result']) && $questionb['result'] == 'true' ? 'data-result="true"': 'data-result="false"'); ?> value="<?php echo esc_attr($questionb['id']) . '||' . esc_attr($questionb['path']) ?>"><?php echo esc_html($questionb['title']) ?></option>
			<?php endif; ?>
		<?php endforeach; ?>

		<option class="finalize"  data-linkid="finalize" value="finalize" <?php echo $option['link']['linkid'] == 'finalize' ? 'selected' : '' ?>><?php esc_html_e( 'Finalize Quiz', 'quizuint' ); ?></option>

	</select>

	<button class="controller remove_essay <?php echo count($option['essay']) > 1 ? '' : 'hidden' ?>" data-id="<?php echo esc_attr($value_es['id'])  ?>" data-command="delete_essay" data-nonce="<?php echo wp_create_nonce('new_essay') ?>">
		<i class="fa fa-remove"></i>
		<?php echo esc_html_e('Delete', 'quizuint') ?>
	</button>

</div>
