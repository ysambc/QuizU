<?php

if ( ! function_exists( 'get_editable_roles' ) ) {
    require_once ABSPATH . 'wp-admin/includes/user.php';
}

function quizu_set_defaults($master_list){
  if (empty(get_option('quizu_settings_permissions')))
  {
    update_option('quizu_settings_permissions', array('administrator' => 'administrator'));
    quizu_caps_updates();
  }

  if (empty(get_option('quizu_settings_default_color')))
  {
    update_option('quizu_settings_default_color', 'dimgray');
  }

  if (empty(get_option('quizu_settings_social_sharing_flag')))
  {
    update_option('quizu_settings_social_sharing_flag', 'true');
  }

  if (empty(get_option('quizu_settings_autosave_quiz_flag')))
  {
    update_option('quizu_settings_autosave_quiz_flag', 'true');
  }

  foreach ($master_list['texts_list'] as $text => $value)
  {
    if (empty(get_option('quizu_settings_texts_'.$text)))
    {
      update_option('quizu_settings_texts_'.$text, $value['default']);
    }
  }

  foreach ($master_list['email_sender_list'] as $email => $value)
  {
    if (empty(get_option('quizu_settings_email_'.$email)))
    {
      update_option('quizu_settings_email_'.$email, $value['default']);
    }
  }
}

function quizu_sanitize_deep(&$value) {
  $value = htmlspecialchars(stripslashes(trim($value)));
}

function quizu_wp_kses($value) {

  $ksesed = wp_kses(htmlspecialchars_decode($value), array(
        'p' => array(),
        'span' => array(
          'style' => array(),
        ),
        'a' => array(
            'href' => array(),
            'title' => array(),
        ),
        'img' => array(
            'class' => array(),
            'alt' => array(),
            'width' => array(),
            'height' => array(),
            'src' => array(),
            'mce-src' => array()
        ),
        'ul' => array(),
        'ol' => array(),
        'li' => array(),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
        'del' => array(),
        'blockquote' => array(),
    )
  );

  return $ksesed;
}

function quizu_get_file_output($file_uri, $linked_quiz = NULL) {
  
  ob_start();
  include($file_uri);
  $content = ob_get_contents();
  ob_end_clean();

  return $content;
}

function quizu_find_linked_quiz(){

  $linked_quiz = get_post_meta(get_the_id(), '_quizu_linked_quiz', true );

  if (empty($linked_quiz)) {

    if (get_post_type() == 'quizu_quiz') {
      $linked_quiz = get_the_id();
    }else{
      $permanent_list = quizu_master_config_list('permanent_list');

      foreach ($permanent_list as $permanent => $value_pe) {
        if (get_post_type() == $permanent) {
          $linked_quiz = get_option( 'quizu_settings_permanent_'.$permanent);
        }
      }
    }
    
  }

  return $linked_quiz;

}

function quizu_aggregate_qr($quizu){
  $aggregated = array();/*Retrieve questions and results to fill in the select box*/

  foreach ($quizu->all_questions as $pathec => $val) {/*Aggregate questions*/
    $pathi = $pathec;
    foreach ($val['questions'] as $key => $value) {
      $title = ucwords(substr($val['name'], 0, 2)) . substr($val['name'], -1, 1) . ':  ' .$value['title'];
      $value['title'] = $title;
      $value['path'] = $pathi;
      $aggregated[$key] = $value;
    }
  }

  foreach ($quizu->all_results as $result => $value) {/*Aggregate results*/
    $title = 'Res: ' . $value['title'];
    $value['result'] = 'true';
    $value['title'] = $title;
    $aggregated[$result] = $value;
  }

  return $aggregated;
}

function quizu_run_string_template($string, $linked_quiz = NULL)
{

  $current_user = wp_get_current_user();

  if (empty($linked_quiz)) {
    $linked_quiz = quizu_find_linked_quiz();
  }

  $templates = array
  (   /*Some templates are replaced in the frontend (email)*/
      '{{admin-email}}' => !empty(get_option('quizu_settings_email_address')) ? get_option('quizu_settings_email_address') : get_bloginfo('admin_email'),
      '{{quiz-title}}' => get_the_title($linked_quiz),
      // '{{result-title}}' => !empty($result) ? $result['title'] :,
      // '{{result-content}}' =>  !empty($result) ? $result['content'] :,
      '{{site-url}}' => get_bloginfo('url')
  );

  foreach ($templates as $template => $replace) 
  {

    while (strpos($string, $template) !== false)
    {
        $string = str_replace($template, $replace, $string);
    }

  }

  return $string;
}

function quizu_run_shortcodes($input){
  preg_match_all('/\[(.*?)\]/', $input, $shortcode);

  if (count($shortcode) > 0) {
    foreach ($shortcode[0] as $found) {
      $input = str_replace($found, do_shortcode($found), $input);
    }
  }

  return $input;
}

function quizu_master_config_list($list = NULL)
{
  $current_user = wp_get_current_user();

  $master_list = array(

    'flag_list' => array
    (
      'social_sharing_flag' => esc_html__('Enable social and email sharing?', 'quizuint'), 
      'autosave_quiz_flag' => esc_html__('Enable auto-save on quiz edit screens?', 'quizuint'), 
      'user_login_flag' => esc_html__('Enable quizzes only for logged in users?', 'quizuint'), 
    ),
    'permanent_list' => array
    (
      'post' => 'posts', 
      'page' => 'pages', 
    ),
    'email_sender_list' => array
    (
      'name' => array('default' => !empty($current_user->user_firstname) && !empty($current_user->user_lastname) ? $current_user->user_firstname .' '. $current_user->user_lastname : $current_user->nickname), 
      'address' => array('default' => $current_user->user_email), 
      'subject' => array('default' => esc_html__('Your results on {{quiz-title}}', 'quizuint')),
      'message' => array('default' => esc_html__('Thank you for taking quiz {{quiz-title}}. Here are your results:&#13;&#13;{{result-title}}&#13;&#13;{{result-content}}', 'quizuint'))
    ),
    'texts_list' => array
    (
      'reset' => array('default' => esc_html__('Reset', 'quizuint'), 'label' => esc_html__('"Reset quiz" button', 'quizuint')), 
      'next' => array('default' => esc_html__('Next', 'quizuint'), 'label' => esc_html__('"Continue quiz" button', 'quizuint')),
      'email' => array('default' => esc_html__('Email result', 'quizuint'), 'label' => esc_html__('"1st email sharing" button', 'quizuint')),
      'send' => array('default' => esc_html__('Send email', 'quizuint'), 'label' => esc_html__('"2nd email sharing" button', 'quizuint')),
      'share' => array('default' => esc_html__('Hey! I just completed this quiz. Try it out!', 'quizuint'), 'label' => esc_html__('"sharing" message', 'quizuint')),
      'post_email' => array('default' => esc_html__('Your results have been sent to: {{user-email}}', 'quizuint'), 'label' => esc_html__('"email sent" message', 'quizuint')),
      'email_error' => array('default' => esc_html__('There was a problem. Please try again.', 'quizuint'), 'label' => esc_html__('"email was not sent" error message', 'quizuint')),
      'total_score' => array('default' => esc_html__('Your score is', 'quizuint'), 'label' => esc_html__('total score message', 'quizuint')),
      'essay_error' => array('default' => esc_html__('Please enter an answer.', 'quizuint'), 'label' => esc_html__('"missing essay answer" error message', 'quizuint')),
      'checked_error' => array('default' => esc_html__('Please select an answer.', 'quizuint'), 'label' => esc_html__('"missing multiple answers" error message', 'quizuint')),
      'error' => array('default' => esc_html__('There seems to be a problem with this quiz. If the problem persists after reloading, please contact support.', 'quizuint'), 'label' => esc_html__('"quiz error" message', 'quizuint')),
      'overlap' => array('default' => esc_html__("This result's score overlaps with", 'quizuint'), 'label' => esc_html__('"overlapping score" error message', 'quizuint')),
      'minimal' => array('default' => esc_html__('Minimal score must be smaller than or equal to maximum score', 'quizuint'), 'label' => esc_html__('"minimal score must be greater" error message', 'quizuint')),
      'integer' => array('default' => esc_html__('Please enter numbers only', 'quizuint'), 'label' => esc_html__('"score is not a number" error message', 'quizuint')),
    )
  );

  if (empty($list))
  {
    return $master_list;
  }else
  {
    return $master_list[$list];
  }
};

function quizu_caps_updates(){

  foreach (get_editable_roles() as $role => $value) {
    if (array_key_exists($role, get_option('quizu_settings_permissions'))) {
      $role_is_capable = true;
    }else{
      $role_is_capable = false;
    }

    $cap = get_role($role);

    if ($role_is_capable) {

      $cap->add_cap('quizu_edit_quizzes');
      $cap->add_cap('edit_quizu');
      $cap->add_cap('edit_quizus');
      $cap->add_cap('delete_quizu');
      $cap->add_cap('delete_quizus');
      $cap->add_cap('edit_published_quizus');
      $cap->add_cap('delete_published_quizus');
      $cap->add_cap('edit_others_quizus');
      $cap->add_cap('delete_others_quizus');
      $cap->add_cap('publish_quizus');

    }else{

      $cap->remove_cap('quizu_edit_quizzes');
      $cap->remove_cap('edit_quizu');
      $cap->remove_cap('edit_quizus');
      $cap->remove_cap('delete_quizu');
      $cap->remove_cap('delete_quizus');
      $cap->remove_cap('edit_published_quizus');
      $cap->remove_cap('delete_published_quizus');
      $cap->remove_cap('edit_others_quizus');
      $cap->remove_cap('delete_others_quizus');
      $cap->remove_cap('publish_quizus');
    
    }
  }
}