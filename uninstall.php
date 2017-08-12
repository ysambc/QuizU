<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$multipq_posts = get_posts( array( 'post_type' => 'mpq_quiz', 'post_status' => get_post_stati(), 'numberposts' => -1));/*Get all mpq posts*/

$regular_linked_posts = get_posts( array( 'post_type' => 'post', 'numberposts' => -1, 'meta_key' => '_mpq_linked_quiz'));/*Get all regular posts*/

$regular_linked_pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'meta_key' => '_mpq_linked_quiz'));/*Get all regular pages*/

foreach( $multipq_posts as $multipq_post ) {
  // Delete mpq posts
  wp_delete_post( $multipq_post->ID, true);
}

foreach ($regular_linked_posts as $regular) {/*Delete linked quiz meta*/
  delete_post_meta( $regular->ID, '_mpq_linked_quiz');
}

foreach ($regular_linked_pages as $regular) {/*Delete linked quiz meta*/
  delete_post_meta( $regular->ID, '_mpq_linked_quiz');
}

delete_option( 'mpq_settings_defaults_stored');
delete_option( 'mpq_settings_default_color');
delete_option( 'mpq_settings_permissions');

foreach ($multipq_master_config_list['flag_list'] as $flag => $value)
{
  delete_option( 'mpq_settings_'.$flag);
}

foreach ($multipq_master_config_list['permanent_list'] as $permanent => $value)
{
  delete_option('mpq_settings_permanent_'.$permanent);
}

foreach ($multipq_master_config_list['email_sender_list'] as $email => $value)
{
  delete_option( 'mpq_settings_email_'.$email);
}

foreach ($multipq_master_config_list['texts_list'] as $text => $value)
{
  delete_option( 'mpq_settings_texts_'.$text);
}