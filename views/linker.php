<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<?php $quizu_posts = get_posts(array('post_type' => 'quizu_quiz', 'number_post' => -1)); ?>

<select class="linker" name="quizu_linked_quiz" style="width: 100%;">
		<option><?php esc_html_e('Select Quiz', 'quizuint' ); ?></option>
	<?php foreach ($quizu_posts as $post): ?>
		<option value="<?php echo $post->ID ?>" <?php echo get_post_meta(get_the_id(), '_quizu_linked_quiz', true ) == $post->ID ? 'selected': ''; ?>><?php echo $post->post_title ?></option>
	<?php endforeach ?>
</select>

