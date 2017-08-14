<div class="path parent" data-path="<?php echo esc_attr($path['id']) ?>" data-color="<?php echo esc_attr($path['color']) ?>">

	<div class="wraper">

		<input class="title path" type="text" value="<?php echo esc_attr_e($path['name'], 'quizuint') ?>" name="quizu_all_questions[<?php echo esc_attr($path['id']) ?>][name]">
		<input class="path_id" type="hidden" value="<?php echo esc_attr($path['id']) ?>" name="quizu_all_questions[<?php echo esc_attr($path_id) ?>][id]">

		<div class="buttons">
			<?php if ($path['id'] !== 'default'): ?>
				<button data-nonce="<?php echo esc_attr(wp_create_nonce( 'delete_path' )); ?>" class="controller delete delete_path" data-command="delete_path">
					<i class="fa fa-remove"></i>
					<?php esc_html_e('Delete', 'quizuint') ?>
				</button>
			<?php endif ?>

			<button data-nonce="<?php echo esc_attr(wp_create_nonce( 'new_question' )); ?>" class="controller new new_question" data-command="new_question">
				<i class="fa fa-plus-circle"></i>
				<?php esc_html_e('New Question', 'quizuint') ?>
			</button>

			<input type="text" class="color_picker" name="quizu_all_questions[<?php echo esc_attr($path_id) ?>][color]" value="<?php echo !empty($path['color']) ? esc_attr($path['color']) : esc_attr(get_option('quizu_settings_default_color')); ?>" />

			<button data-nonce="<?php echo esc_attr(wp_create_nonce( 'update_path' )); ?>" class="controller update update_path" data-command="update_path">
				<i class="fa fa-save"></i>
				<?php esc_html_e('Save', 'quizuint') ?>
			</button>
		</div>

		<i class="fa fa-compress collapse"></i>

	</div>

	<ul class="questions container ui-sortable" data-path="<?php echo esc_attr($path['id']) ?>">
		<?php

			if (isset($new_question_switch) && $new_question_switch == true) {
				$this->new_question($path['id']);
			}

			foreach (array_reverse($path['questions']) as $question_id => $question){/*Reversed for display*/
				include( plugin_dir_path( __DIR__ ) . 'views/question.php');
			}

		 ?>
	</ul>
	
</div>