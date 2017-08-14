<?php

	if (isset($_POST['quizu_settings']) && !wp_verify_nonce( $_POST['quizu_settings']['nonce'], 'quizu_settings_save' )) {
		exit;
	}

	wp_enqueue_style('quizu_admin_css');

	$master_config_list = quizu_master_config_list();

	if (isset($_POST['quizu_settings'])) {

		$stripedvars = $_POST['quizu_settings'];

		array_walk_recursive($stripedvars, 'quizu_sanitize_deep');

		foreach ($stripedvars['permanent'] as $permanent => $value) 
		{
			if (is_numeric($value)) {
				 $stripedvars['permanent'][$permanent] =  sanitize_text_field($value);
			}else{
				$stripedvars['permanent'][$permanent] = '';
			}
		}


		if (isset($stripedvars['flags'])) 
		{
			foreach ($stripedvars['flags'] as $flag => $value) {
				if ( $value == 'true') {
					$stripedvars['flags'][$flag] =  'true';
				}else{
					$stripedvars['flags'][$flag] = 'false';
				}
			}
		}


		if (isset($stripedvars['email_sender'])) 
		{
			foreach ($stripedvars['email_sender'] as $email => $value) {
			
				if (!empty($value)) {
					$stripedvars['email_sender'][$email] = sanitize_textarea_field($value);
				}else{
					$stripedvars['email_sender'][$email] = $master_config_list['email_sender_list'][$email]['default'];
				}
			}
		}


		if (isset($stripedvars['texts']))
		{

			foreach ($stripedvars['texts'] as $text => $value) {

				if (!empty($value)) {

					$stripedvars['texts'][$text] = sanitize_textarea_field($value);
				}else{
					$stripedvars['texts'][$text] = $master_config_list['texts_list'][$text]['default'];
				}
			}
		}

		if (strlen($stripedvars['default_color']) == 7)
		{
			 $stripedvars['default_color'] =  sanitize_text_field($stripedvars['default_color']);
		}else{
			$stripedvars['default_color'] = '';
		}

		if (!empty($stripedvars['flags'])) {
			foreach ($master_config_list['flag_list'] as $flag => $value) {
				if (empty($stripedvars['flags'][$flag]))
				{
					$stripedvars['flags'][$flag] = 'false';
				}
			}
		}

		// update_option( 'quizu_settings_auto_email_flag', $stripedvars['auto_email_flag'] );
		update_option( 'quizu_settings_permissions', $stripedvars['permissions']);
		quizu_caps_updates();

		update_option( 'quizu_settings_default_color', $stripedvars['default_color']);

		
		foreach ($stripedvars['flags'] as $flag => $value)
		{
			update_option( 'quizu_settings_'.$flag, $value);
		}

		
		foreach ($stripedvars['permanent'] as $permanent => $value)
		{
			update_option('quizu_settings_permanent_'.$permanent, $value);
		}


		foreach ($stripedvars['email_sender'] as $email => $value)
		{
			update_option( 'quizu_settings_email_'.$email, $value);
		}

		foreach ($stripedvars['texts'] as $text => $value)
		{
			update_option( 'quizu_settings_texts_'.$text, $value);
		}

	}


	// $quizu_auto_email_flag = get_option( 'quizu_settings_auto_email_flag');
	$permissions = get_option('quizu_settings_permissions');
	$default_color = get_option( 'quizu_settings_default_color');

	foreach ($master_config_list['permanent_list'] as $permanent => $value)
	{
		$permanents[$permanent] = get_option('quizu_settings_permanent_'.$permanent);
	}

	foreach ($master_config_list['flag_list'] as $flag => $value)
	{
		$flags[$flag]['label'] = $value;
		$flags[$flag]['value'] = get_option( 'quizu_settings_'.$flag);
	}

	foreach ($master_config_list['texts_list'] as $text => $value)
	{
		$texts[$text] = get_option('quizu_settings_texts_'.$text);
	}

	foreach ($master_config_list['email_sender_list'] as $email => $value)
	{
		$emails[$email] = get_option('quizu_settings_email_'.$email);
	}

 ?>

<div id="quizu_settings" class="quizu_admin_screen">

	<h1><?php esc_html_e('QuizU settings', 'quizuint') ?></h1>

	<form method="post">

		<div class="core_settings settings">
			<?php foreach ($flags as $flag => $value): ?>

				<div class="option">

					<p class="label"><?php esc_html_e($value['label'], 'quizuint') ?></p>

					<label class="switch" labelfor="<?php echo '#quizu_flags_'.$flag ?>">
						<div>
						 	<input id="<?php echo 'quizu_flags_'.$flag ?>" type="checkbox" value="true" name="quizu_settings[flags][<?php echo $flag ?>]" <?php echo $flags[$flag]['value'] == 'true' ? 'checked' : '' ; ?>>
						 	<div class="slider round"></div>
						</div>
					</label>

				</div>

			<?php endforeach ?>

			<div class="option">

				<label labelfor="#quizu_default_color">
					<?php esc_html_e('Default color for buttons and options', 'quizuint') ?>
				</label>

				<input id="quizu_default_color" type="text" class="quizu_color_picker" name="quizu_settings[default_color]" value="<?php echo $default_color ?>" />

			</div>

			<div class="option">

				<p class="label"><?php esc_html_e('Roles Allowed to Handle Quizzes', 'quizuint') ?></p>
				
				<div class="container">


					<?php foreach (get_editable_roles() as $role => $value): ?>
						<label class="permission" labelfor="#quizu_permissions_<?php echo $role ?>">
							<input id="quizu_permissions_<?php echo $role ?>" type="checkbox" name="quizu_settings[permissions][<?php echo $role ?>]" value="<?php echo $role ?>" <?php echo !empty($permissions) && array_key_exists($role, $permissions) ? 'checked': ''; ?>>
							<?php echo $value['name'] ?>
						</label>
					<?php endforeach ?>

					
				</div>

			</div>

			<?php foreach ($master_config_list['permanent_list'] as $permanent => $value_pe): ?>
				
				<div class="option">
					
					<label labelfor="<?php echo '#quizu_permanent_'.$permanent ?>">
						<?php esc_html_e('Show this quiz in all', 'quizuint'); echo ' ' . $value_pe; ?>
					</label>

					<select id="<?php echo 'quizu_permanent_'.$permanent ?>" name="quizu_settings[permanent][<?php echo $permanent ?>]">
							
							<option value="">---</option>

						<?php $args = array( 'post_type' => 'quizu_quiz', 'numberposts' => -1); $postss = get_posts( $args );  ?>
						<?php foreach ($postss as $quiz => $value_qu): ?>

							<option value="<?php echo esc_attr($value_qu->ID) ?>" <?php echo $permanents[$permanent] == $value_qu->ID ? 'selected' : '' ; ?>><?php echo esc_html($value_qu->post_title) ?></option>
							
						<?php endforeach; ?>
						
					</select>

				</div>
					
			<?php endforeach ?>

		</div>

		<div class="tag_notice">
			<p><?php esc_html_e('You may use these tags in all "text-areas" beyond this point (except email sender&#8217;s name and address). Leave "text-areas" blank to return to default:', 'quizuint') ?></p>
			<p>{{quiz-title}} &ensp;&ensp;<span>||</span>&ensp;&ensp; {{result-title}} &ensp;&ensp;<span>||</span>&ensp;&ensp; {{result-content}} &ensp;&ensp;<span>||</span>&ensp;&ensp; {{user-email}} &ensp;&ensp;<span>||</span>&ensp;&ensp; {{admin-email}} &ensp;&ensp;<span>||</span>&ensp;&ensp; {{site-url}}</p>
		</div>

		<div class="email_settings settings">

			<h2><?php esc_html_e('Email settings', 'quizuint') ?></h2>
	
			<?php foreach ($master_config_list['email_sender_list'] as $email => $value): ?>
				
				<div class="option">
					
					<label labelfor="<?php echo '#email_sender_'.$email ?>">
						<?php echo esc_html_e('Email sender', 'quizuint').' '.str_replace('_', ' ', $email) ?>
					</label>

					<textarea id="<?php echo 'email_sender_'.$email ?>" name="quizu_settings[email_sender][<?php echo $email ?>]"><?php echo $emails[$email]  ?></textarea>

				</div>

			<?php endforeach ?>

		</div>

		<div class="texts_settings settings">

			<h2><?php esc_html_e('Text display settings', 'quizuint') ?></h2>

			<?php foreach($master_config_list['texts_list'] as $text => $value): ?>
				
				<div class="option">

					<label labelfor="<?php echo '#quizu_texts_'.$text ?>">
						<?php esc_html_e('Text for '.$value['label'], 'quizuint') ?>
					</label>

					<textarea id="<?php echo 'quizu_texts_'.$text ?>" name="quizu_settings[texts][<?php echo $text ?>]"><?php echo $texts[$text] ?></textarea>

				</div>

			<?php endforeach ?>

		</div>

		<button class="submit button button-primary"><?php esc_html_e('Save Changes', 'quizuint') ?></button>

		<?php wp_nonce_field( 'quizu_settings_save', 'quizu_settings[nonce]'); ?>

	</form>

	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.quizu_color_picker').wpColorPicker({});
		});
	</script>

</div>