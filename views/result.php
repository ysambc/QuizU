<div class="result parent" data-question="<?php echo esc_attr($result['id']) ?>">

	<input class="id" type="hidden" name="mpq_all_results[<?php echo esc_attr($result['id']) ?>][id]" value="<?php echo esc_attr($result['id']) ?>">
	<input class="title result" type="text" name="mpq_all_results[<?php echo esc_attr($result['id']) ?>][title]" value="<?php echo esc_attr($result['title']) ?>">

	<?php 

	wp_editor( multipq_wp_kses($result['content']), 
		'tinymce_'.esc_html($result['id']), array(
	        'tinymce' => array(
	            'init_instance_callback' => 'function(ed, e) {
	        		if (mpqObj.flags.autosave == "true") {
	        			ed.on("change", function(e){
	                    	if (typeof mceEditorCounter !== "undefined") {
	                    	  clearTimeout(mceEditorCounter);
	                    	}

	                    	mceEditorCounter = setTimeout(function(){
	                    		jQuery(e.target.iframeElement).contents().find("span[data-mce-style]").each(function(){
	                    		  jQuery(this).attr("style", jQuery(this).attr("data-mce-style"));
	                    		});
	                        	jQuery(e.target.targetElm).closest(".parent.result").find(".controller.update").click();
	                    	}, 500);
	                    });
	        		}
	            }'
			),
	    	'textarea_name' => 'mpq_all_results['. esc_attr($result['id']).'][content]'
        )
	);

	?>

	<?php 

		switch (get_post_meta($mpq->mpq_id, '_mpq_result_criteria_flag', true)) {
			case 'results_by_option':
				$class = 'results_by_option';
				break;
			
			default:
				$class = 'results_by_total';
				break;
		}

	 ?>

	<div class="buttons <?php echo $class ?>">
		
		<button data-nonce="<?php echo esc_attr(wp_create_nonce( 'delete_result' )); ?>" class="controller delete delete_result" data-command="delete_result">
			<i class="fa fa-remove"></i>
			<?php esc_html_e('Delete', 'quizuint') ?>
		</button>

		<button data-nonce="<?php echo esc_attr(wp_create_nonce( 'update_result' )); ?>" class="controller update update_result" data-command="update_result">
			<i class="fa fa-save"></i>
			<?php esc_html_e('Save', 'quizuint') ?>
		</button>

		<div class="scores">
			<label labelfor="#min_<?php echo esc_attr($result['id']) ?>"><?php echo esc_html_e('Min. Score', 'quizuint') ?></label>
			<input class="range min" type="text" id="min_<?php echo esc_attr($result['id']) ?>" value="<?php echo !empty($result['score']['min']) ? esc_attr($result['score']['min']) : 0 ?>" name="mpq_all_results[<?php echo esc_attr($result['id']) ?>][score][min]">
			<label labelfor="#max_<?php echo esc_attr($result['id']) ?>"><?php echo esc_html_e('Max. Score', 'quizuint') ?></label>
			<input class="range max" type="text" id="max_<?php echo esc_attr($result['id']) ?>" value="<?php echo !empty($result['score']['max']) ? esc_attr($result['score']['max']) : 0 ?>" name="mpq_all_results[<?php echo esc_attr($result['id']) ?>][score][max]">
		</div>

		<?php 

			$i = 0;
			$number = 0;
			$counter = 0;

			foreach ($mpq->all_questions as $path => $value_pa) {
				foreach ($value_pa['questions'] as $question) {
					foreach ($question['options'] as $option) {
						$counter++;

						if ($counter >= $number) {
							$number = $counter;
						}
					}
					$counter = 0;
				}
			}

		 ?>

		<div class="highest">
			<select name="mpq_all_results[<?php echo esc_attr($result['id']) ?>][highest]">
				<option value=""><?php esc_html_e('Selected option') ?></option>
				<?php while ( $i < ($number - 1)):?>
				<option <?php echo $result['highest'] == 'option_'.($i) ? 'selected' : '' ?> value="<?php echo 'option_'.($i) ?>"><?php esc_html_e('Option', 'quizuint'); echo ' '.($i + 1) ?></option>
				<?php $i++; endwhile; ?>
				<option <?php echo $result['highest'] == 'option_e' ? 'selected' : '' ?> value="<?php echo 'option_e' ?>"><?php esc_html_e('Essay option', 'quizuint'); ?></option>
			</select>
		</div>

	</div>

</div>