<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<?php 

	$quizu = new quizu_admin_ajax_controller_model(get_the_id());
	$user_login_flag = get_post_meta(get_the_id(), '_quizu_user_login_flag', true);
	$show_scores_flag = get_post_meta(get_the_id(), '_quizu_show_scores_flag', true);
	$result_criteria_flag = get_post_meta($quizu->quizu_id, '_quizu_result_criteria_flag', true);

?>

<div class="quizu_admin_screen">
	<input id="update_quiz_tc" type="hidden" name="update_quiz_tc" value="<?php echo wp_create_nonce('update_quiz_tc' ); ?>">
	<input id="quizu_id" type="hidden" name="quizu_id" value="<?php echo esc_attr(get_the_id()) ?>">

	<p class="heading questions_heading">
		Questions
	</p>

	<p class="label"><?php esc_html_e('Show only for logged in users', 'quizuint') ?></p>
	<label class="switch main">
	 	<input class="login_flag controller flag" data-command="update_login_flag" data-flag="<?php echo $user_login_flag == 'true' ? 'false' : 'true' ; ?>" data-nonce="<?php echo wp_create_nonce('update_login_flag') ?>" name="quizu_all_flags[user_login_flag]" type="checkbox" <?php echo $user_login_flag == 'true' ? 'checked' : '' ; ?>>
	 	<div class="slider round"></div>
	</label>

	<button data-nonce="<?php echo esc_attr(wp_create_nonce( 'new_path' )); ?>" class="controller new path new_path" data-command="new_path">
		<i class="fa fa-plus-circle"></i>
		<?php esc_html_e('New Branch', 'quizuint') ?>
	</button>

	<div class="clear"></div>

	<?php foreach (array_reverse($quizu->all_questions) as $path_id => $path): ?>
		<?php include( plugin_dir_path( __DIR__ ) . 'views/path.php'); ?>
	<?php endforeach; ?>

	<hr class="admin_divider">

	<p class="heading results_heading">
		Results
	</p>

	<p class="label">
		<?php esc_html_e('Determine quiz results by', 'quizuint'); echo ':'; ?>
	</p>

	<select id="results_criteria" class="results criteria" name="quizu_all_flags[result_criteria_flag]" data-flag="<?php echo $result_criteria_flag ?>" data-command="update_result_criteria_flag" data-nonce="<?php echo wp_create_nonce('update_result_criteria_flag') ?>">
		<option value="results_by_path" <?php echo $result_criteria_flag == 'results_by_path' ? 'selected' : '' ?>><?php esc_html_e('Branch', 'quizuint') ?></option>
		<option value="results_by_total" <?php echo $result_criteria_flag == 'results_by_total' ? 'selected' : '' ?>><?php esc_html_e('Score total', 'quizuint') ?></option>
		<option value="results_by_option" <?php echo $result_criteria_flag == 'results_by_option' ? 'selected' : '' ?>><?php esc_html_e('Highest option', 'quizuint') ?></option>
	</select>

	<br>

	<p class="label"><?php esc_html_e('Show scores after quiz is completed', 'quizuint') ?></p>

	<label class="switch main">
	 	<input class="show_scores_flag controller flag" data-command="update_show_scores_flag" data-flag="<?php echo $show_scores_flag == 'true' ? 'false' : 'true' ; ?>" data-nonce="<?php echo wp_create_nonce('update_show_scores_flag') ?>" name="quizu_all_flags[show_scores_flag]" type="checkbox" <?php echo $show_scores_flag == 'true' ? 'checked' : '' ; ?>>
	 	<div class="slider round"></div>
	</label>

	<?php foreach ($quizu->all_results as $result): ?>
		<?php include( plugin_dir_path( __DIR__ ) . 'views/result.php'); ?>
	<?php endforeach; ?>

	<button data-nonce="<?php echo esc_attr(wp_create_nonce( 'new_result' )); ?>" class="controller new result new_result" data-command="new_result">
		<i class="fa fa-plus-circle"></i>
		<?php esc_html_e('New Result', 'quizuint') ?>
	</button>
	<div class="clear"></div>
</div>