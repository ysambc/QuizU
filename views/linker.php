<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>
<?php $mpq_posts = get_posts(array('post_type' => 'mpq_quiz', 'number_post' => -1)); ?>

<select class="linker" name="mpq_linked_quiz">
		<option><?php esc_html_e('Select Quiz', 'quizuint' ); ?></option>
	<?php foreach ($mpq_posts as $post): ?>
		<option value="<?php echo $post->ID ?>" <?php echo esc_attr(get_post_meta(get_the_id(), '_mpq_linked_quiz', true ) == $post->ID ? 'selected': ''); ?>><?php echo $post->post_title ?></option>
	<?php endforeach ?>
</select>

