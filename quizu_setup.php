<?php 

defined('ABSPATH') or die("Cannot access pages directly.");

// DEFAULTS SETTINGS----------------------------------------------------------------------------------------------------------------

$master_list = quizu_master_config_list();
$permited_roles = get_option('quizu_settings_permissions');

if (empty(get_option('quizu_settings_defaults_stored'))) {
  quizu_set_defaults($master_list);
  update_option('quizu_settings_defaults_stored', 'true');
}

if (is_array(array_intersect_key(wp_get_current_user()->roles, $permited_roles))) {
  $current_user_is_capable = true;
}else{
  $current_user_is_capable = false;
}

// REGISTER POST TYPES----------------------------------------------------------------------------------------------------------------

function quizu_post_types_setup(){/*Define Quiz post type*/
    $args = array(
      'public' => true,
      'has_archive' => true,
      'show_in_menu' => 'quizu_main.php',
      'label'  => esc_html__('Branched Quizes', 'quizuint'),
      'supports' => array('editor', 'title'),
      'capability_type' => 'quizu',
      'map_meta_cap' => true
    );

    register_post_type( 'quizu_quiz', $args );
}
add_action( 'init', 'quizu_post_types_setup' );


// INTERNATIONALIZATION----------------------------------------------------------------------------------------------------------------

load_plugin_textdomain('quizuint', false, dirname(plugin_basename(__FILE__ )) . '/languages');


// REGISTER QUIZES WIDGET----------------------------------------------------------------------------------------------------------------

// register Foo_Widget widget
function register_quizu_widget() {
    register_widget( 'quizu_Widget' );
}
add_action( 'widgets_init', 'register_quizu_widget' );

/**
 * Adds quizu widget.
 */
class quizu_Widget extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  function __construct() {
    parent::__construct(
      'quizu_widget', // Base ID
      esc_html__( 'QuizU Widget', 'quizuint' ), // Name
      array( 'description' => esc_html__( 'Use to display your branched quizzes', 'quizuint' ), ) // Args
    );

  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */

  private function get_master_list($list){
    return quizu_master_config_list($list);
  }

  public function widget( $args, $instance ) {

    $quizu_login_restriction_flag = false;

    if (  
        (
            get_option('quizu_settings_user_login_flag') == 'true'
        ||  get_post_meta(quizu_find_linked_quiz(), '_quizu_user_login_flag', true) == 'true'
        )
          &&  !is_user_logged_in()
      ) 
    {
      $quizu_login_restriction_flag = true;
    }

    if (!$quizu_login_restriction_flag) {
      echo $args['before_widget'];
    }

    if ( ! empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    }
    $quizu_in_sidebar = true;
    include( plugin_dir_path( __FILE__ ) . 'views/shortcode.php');
    if (!$quizu_login_restriction_flag) {
      echo $args['after_widget'];
    }
    unset($quizu_in_sidebar);
  }

  public function form( $instance ) {/*Input for widget title*/
    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    ?>
      <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'quizuint' ); ?></label> 
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
      </p>

    <?php 
  }

  public function update( $new_instance, $old_instance ) {/*Update title*/
    $instance = array();
    $instance['title'] = (!empty( $new_instance['title'] )) ? strip_tags($new_instance['title'] ) : '';

    return $instance;
  }

}


// ADMIN COMPONENTS----------------------------------------------------------------------------------------------------------------

if (is_user_logged_in() && $current_user_is_capable) {

  // ENQUE AND LOCALIZE ADMIN SCRIPTS AND STYLES----------------------------------------------------------------------------------------------------------------

  function quizu_admin_enqueues($hook) {/*Enqueues for admin*/

    if ($hook != ('quizu_quiz' || 'quizu_settings') && get_post_type() != 'quizu_quiz') {
      return;
    }

    wp_enqueue_style('quizu-admin-css', plugins_url( '/includes/css/quizu-admin.css', __FILE__ ));
    wp_enqueue_style('fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style( 'wp-color-picker');

    wp_enqueue_script('quizu-admin-js', plugins_url( '/includes/js/quizu-admin.js', __FILE__ ), array('jquery'), null, true);
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('wp-color-picker');

    wp_enqueue_media();

    wp_localize_script( 'quizu-admin-js', 'quizuObj', array(
      'overlap' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_overlap')))),
      'minimal' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_minimal')))),
      'integer' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_integer')))),
      'option' => esc_html__('Option', 'quizuint'),
      'flags' => array(
        'autosave' => esc_html(get_option('quizu_settings_autosave_quiz_flag')),
      ),
    ));
  }

  add_action('admin_enqueue_scripts','quizu_admin_enqueues');

  // REGISTER CUSTOM COLUMNS----------------------------------------------------------------------------------------------------------------

  // Add the custom columns to the book post type:

    function quizu_quiz_list_columns($columns) {
        $columns['quizu_shortcode'] = __( 'Shortcode', 'quizuint' );
        return $columns;
    }

    add_filter( 'manage_quizu_quiz_posts_columns' , 'quizu_quiz_list_columns', 10, 2 );

    // Add the data to the custom columns for the book post type:

    function quizu_quiz_archive_column_content($column, $id){

      switch ($column) {
        case 'quizu_shortcode':
            echo '<input type="text" value="[quizu id='.'&#34;'.$id.'&#34;'.']" size="15" readonly onclick="this.select()">';
          break;

      }
    }

    add_action( 'manage_quizu_quiz_posts_custom_column' , 'quizu_quiz_archive_column_content', 10, 2 );


  // POST CLONE-----------------------------------------------------------------------------------------------------------------

  function quizu_quiz_archive_clone_quiz(){

   if (is_post_type_archive('quizu_quiz') && isset($_GET['quizu_settings']['quizu_clone'])) {
      $stripedvars = $_GET['quizu_settings'];

      array_walk_recursive($stripedvars, 'quizu_sanitize_deep');

      $current_post = get_post($stripedvars['quizu_clone']);

      $current_user = wp_get_current_user();

      $new_post_author = $current_user->ID;

      if (isset( $current_post ) && $current_post != null) {
       
          /*
           * new post data array
           */
          $args = array(
            'comment_status' => $current_post->comment_status,
            'ping_status'    => $current_post->ping_status,
            'post_author'    => $new_post_author,
            'post_content'   => $current_post->post_content,
            'post_excerpt'   => $current_post->post_excerpt,
            'post_parent'    => $current_post->post_parent,
            'post_password'  => $current_post->post_password,
            'post_status'    => 'draft',
            'post_title'     => $current_post->post_title,
            'post_type'      => $current_post->post_type,
            'to_ping'        => $current_post->to_ping,
            'menu_order'     => $current_post->menu_order
          );
       
          /*
           * insert the post by wp_insert_post() function
           */
          $new_post_id = wp_insert_post($args);

          /*
           * get all current post terms ad set them to the new post draft
           */
          $taxonomies = get_object_taxonomies($current_post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
          foreach ($taxonomies as $taxonomy) {
            $current_post_terms = wp_get_object_terms($current_post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $current_post_terms, $taxonomy, false);
          }
       
          /*
           * duplicate all post meta just in two SQL queries
           */
          $current_post_meta = get_post_meta($current_post->ID);

          foreach ($current_post_meta as $meta => $value) {
            update_post_meta($new_post_id, $meta, get_post_meta($current_post->ID, $meta, true));
          }
       
       
          /*
           * finally, redirect to the edit post screen for the new draft
           */
          wp_redirect( admin_url( 'edit.php?&post_type=quizu_quiz') );
          exit;
        } else {
          wp_die(esc_html__('Post creation failed, could not find original post', 'quizuint') . ': ' . $current_post_id);
        }
    }
  }

  add_action( 'pre_get_posts', 'quizu_quiz_archive_clone_quiz');

  function quizu_duplicate_quiz_link_button( $actions, $post ) {
    if (current_user_can('edit_posts')) {
      $actions['duplicate'] = '<a href="edit.php?&post_type=quizu_quiz&quizu_settings%5Bquizu_clone%5D='.$post->ID.'">'.esc_html__('Clone', 'quizuint').'</a>';
    }
    return $actions;
  }
   
  add_filter( 'post_row_actions', 'quizu_duplicate_quiz_link_button', 10, 2 );


  // SETUP quizu POST BEHAVIOUR-----------------------------------------------------------------------------------------------------------------

  function quizu_disable_mce_buttons( $opt ) {
      //set button that will be show in row 1
      $opt['theme_advanced_buttons1'] = 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,wp_more,|,spellchecker,wp_fullscreen,wp_adv,separator';
      return $opt;
  }

  add_filter('tiny_mce_before_init', 'quizu_disable_mce_buttons');

  
  // QUIZ PREVIEW-----------------------------------------------------------------------------------------------------------------

  function quizu_quiz_preview($content){
    if (get_post_type() == 'quizu_quiz') {
      remove_filter('the_content', 'quizu_quiz_preview');
      $content = do_shortcode('[quizu id="'.get_the_id().'"]');
    }
    return $content;
  }
  add_filter('the_content', 'quizu_quiz_preview');

  // SETUP quizu POST BEHAVIOUR-----------------------------------------------------------------------------------------------------------------

  function quizu_quiz_save_behaviour() {/*Define Quiz on save behaviour*/

    if (get_post_type() == 'quizu_quiz') {/*Check if target is empty*/

      if(isset($_POST[ 'quizu_quiz_save_behaviour' ]) && !wp_verify_nonce( $_POST[ 'quizu_quiz_save_behaviour' ], basename( __FILE__ ) )){
        return;
      };

      $all_questions = $_POST['quizu_all_questions'];

      $all_results = $_POST['quizu_all_results'];

      $all_flags = array(
          'user_login_flag',
          'show_scores_flag',
          'result_criteria_flag',
      );

      foreach ($all_flags as $flag) {
        update_post_meta(get_the_id(), '_quizu_'.$flag, $_POST['quizu_all_flags'][$flag] == 'on' ? 'true' : $_POST['quizu_all_flags'][$flag]);
      }

      array_walk_recursive($all_questions, "quizu_sanitize_deep");/*Sanitize array with htmlspecialchars*/
      array_walk_recursive($all_results, "quizu_sanitize_deep");/*Sanitize array with htmlspecialchars*/

      $all_questions_R = array_reverse($all_questions);/*Reverse array from display order*/

      foreach ($all_questions_R as $path) {
        $questionsR = array_reverse($path['questions']);/*Reverse array from display order*/
        $all_questions_R[$path['id']]['questions'] = $questionsR;/*Update questions*/
        foreach ($questionsR as $question) {

          if (!empty($question['flags']['essay_flag']) && $question['flags']['essay_flag'] == 'on') {
            $all_questions_R[$path['id']]['questions'][$question['id']]['flags']['essay_flag'] = 'true';/*Update options*/
          }else{
            $all_questions_R[$path['id']]['questions'][$question['id']]['flags']['essay_flag'] = 'false';/*Update options*/
          }
          
          if (!empty($question['flags']['multiple_choice_flag']) && $question['flags']['multiple_choice_flag'] == 'on') {
            $all_questions_R[$path['id']]['questions'][$question['id']]['flags']['multiple_choice_flag'] = 'true';/*Update options*/
          }else{
            $all_questions_R[$path['id']]['questions'][$question['id']]['flags']['multiple_choice_flag'] = 'false';/*Update options*/
          }

          $options = $question['options'];/*Reverse array from display order*/

          foreach ($options as $option) {
            $split = explode('||', $option['link']);
            $option['link'] = array();
            $option['link']['linkid'] = $split[0];
            $option['link']['linkpath'] = $split[1];

            if (!empty($option['essay_ops'])) {
              foreach ($option['essay_ops'] as $essay) {
                $split2 = explode('||', $essay['link']);
                $essay['link'] = array();
                $essay['link']['linkid'] = $split2[0];
                $essay['link']['linkpath'] = $split2[1];

                $option['essay_ops'][$essay['id']] = $essay;
              }
            }

            $options[$option['id']] = $option;
          }

          $all_questions_R[$path['id']]['questions'][$question['id']]['options'] = $options;/*Update options*/
        }
      }

      foreach ($all_results as $result => $value_re) {
        foreach ($all_results as $result_2 => $value_re_2) {
          if (
                  $value_re['id'] != $value_re_2['id'] 
              &&  intval($value_re_2['score']['min']) + intval($value_re_2['score']['max']) != 0
              &&  intval($value_re['score']['min']) + intval($value_re['score']['max']) != 0

            ) {
            if (max($value_re['score']['min'], $value_re_2['score']['min']) <= min($value_re['score']['max'], $value_re_2['score']['max'])) {
              
              $all_results[$value_re['id']]['score']['min'] = 0;
              $all_results[$value_re['id']]['score']['max'] = 0;

              $all_results[$value_re_2['id']]['score']['min'] = 0;
              $all_results[$value_re_2['id']]['score']['max'] = 0;

            }
          };
        }
      }

      update_post_meta(get_the_id(), '_quizu_questions', $all_questions_R);/*Insert updated questions*/
      update_post_meta(get_the_id(), '_quizu_results', $all_results);/*Insert updated results*/

      if (empty(get_post_meta(get_the_id(), '_quizu_questions', true))) {/*Check if target is empty*/
        update_post_meta( get_the_id(), '_quizu_questions', array());/*If empty, fill with an empty array*/
      }

      if (empty(get_post_meta(get_the_id(), '_quizu_results', true))) {/*Check if target is empty*/
        update_post_meta( get_the_id(), '_quizu_results', array());/*If empty, fill with an empty array*/
      }
    }

    if (get_post_type() == ('post' || 'page')) {/*If regular post or page*/

      $quizu_linked_quiz = htmlspecialchars((int)$_POST['quizu_linked_quiz']);/*Get linked quiz ID*/

      update_post_meta(get_the_id(), '_quizu_linked_quiz', $quizu_linked_quiz);/*Store linked quiz ID*/
    }
  }
  add_action( 'save_post', 'quizu_quiz_save_behaviour' );

  // REGISTER ADMIN META-BOXES----------------------------------------------------------------------------------------------------------------

  function quizu_questions_metabox(){/*Register Quiz config admin panel meta-boxes*/
    add_meta_box( 'quizu_questions', esc_html__('Branched Quiz', 'quizuint') , 'quizu_questions_metabox_content', 'quizu_quiz', 'normal' , 'high');
    add_meta_box( 'quizu_linked_quizes', esc_html__('QuizU Linked Quiz', 'quizuint') , 'quizu_linked_quizzes_metabox_content', array('post', 'page'), 'side' , 'high');
  }
  add_action ( 'add_meta_boxes' , 'quizu_questions_metabox' ) ;

  function quizu_questions_metabox_content(){/*Define quiz config admin panel contents*/
    wp_nonce_field( basename( __FILE__ ), 'quizu_quiz_save_behaviour' );
    include( plugin_dir_path( __FILE__ ) . 'views/admin.php');
  }

  function quizu_linked_quizzes_metabox_content(){/*Define quiz config admin panel contents*/
    wp_nonce_field( basename( __FILE__ ), 'quizu_quiz_save_behaviour' );
    include( plugin_dir_path( __FILE__ ) . 'views/linker.php');
  }

  // REGISTER ADMIN PAGE----------------------------------------------------------------------------------------------------------------


  function quizu_settings_menu() {

    add_menu_page(
      esc_html__('Branched Quizzes', 'quizuint'),
      'QuizU',
      'quizu_edit_quizzes',
      'quizu_main.php',
      NULL,
      'dashicons-tickets',
      6
    );

    add_submenu_page(
        'quizu_main.php',
        esc_html__('Add New Branched Quiz', 'quizuint'), /*page title*/
        esc_html__('New Quiz', 'quizuint'), /*menu title*/
        'quizu_edit_quizzes', /*roles and capabiliyt needed*/
        'post-new.php?post_type=quizu_quiz',
        '' /*replace with your own function*/
    );

    add_submenu_page(
        'quizu_main.php',
        esc_html__('QuizU Settings', 'quizuint'), /*page title*/
        esc_html__('Settings', 'quizuint'), /*menu title*/
        'quizu_edit_quizzes', /*roles and capabiliyt needed*/
        'quizu_settings',
        'quizu_settings_page_content' /*replace with your own function*/
    );

  }

  function quizu_settings_page_content(){
    include( plugin_dir_path( __FILE__ ) . 'views/settings.php');
  }

  add_action( 'admin_menu', 'quizu_settings_menu' );

}

// REGISTER QUIZES SHORTCODE----------------------------------------------------------------------------------------------------------------

function quizu_quiz_shortcode($atts) {/*Define and register front end Quiz shortcode*/
  $quizu_atts = shortcode_atts(array(
    'id' => '',
  ), $atts );

  $linked_quiz = $quizu_atts['id'];

  ob_start();
  include(plugin_dir_path( __FILE__ ) . 'views/shortcode.php');
  $output = ob_get_contents();
  ob_end_clean();

  return $output;

}
add_shortcode( 'quizu', 'quizu_quiz_shortcode');


// FRONT-END COMPONENTS----------------------------------------------------------------------------------------------------------------

if (!is_admin()) {

  // REGISTER AND ENQUEUE FRONT SCRIPTS AND STYLES----------------------------------------------------------------------------------------------------------------

  function quizu_front_enqueues($linked_quiz) {/*Enqueues for front*/
    
    wp_enqueue_script('quizu-front-js', plugins_url( '/includes/js/quizu-front.js', __FILE__ ), array('jquery'), null, true);
    wp_enqueue_script('jssocials', 'https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js', array('jquery'), null, true);
    wp_enqueue_style('quizu-front-css', plugins_url( '/includes/css/quizu-front.css', __FILE__ ));
    wp_enqueue_style('jsscoials-flat', 'https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials-theme-flat.css');

    $quizu_current_user = wp_get_current_user();
    $linked_quiz = quizu_find_linked_quiz();

    wp_localize_script( 'quizu-front-js', 'quizuObj', array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'defaultColor' => esc_html(get_option('quizu_settings_default_color')),
      'reset' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_reset'), 'quizuint'))),
      'next' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_next'), 'quizuint'))),
      'option' => esc_html__('Option', 'quizuint'),
      'optionEssay' => esc_html__('Essay op.', 'quizuint'),
      'essayError' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_essay_error'), 'quizuint'))),
      'checkedError' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_checked_error'), 'quizuint'))),
      'error' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_error'), 'quizuint'))),
      'share' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_share'), 'quizuint'))),
      'email' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_email'), 'quizuint'))),
      'send' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_send'), 'quizuint'))),
      'senderEmail' => esc_html(get_option('quizu_settings_email_address')),
      'senderName' => esc_html(get_option('quizu_settings_email_name')),
      'userEmail' => sanitize_email($quizu_current_user->user_email),
      'emailSubject' => htmlspecialchars_decode(quizu_run_string_template(get_option('quizu_settings_email_subject'))),
      'postEmail' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_post_email'), 'quizuint'))),
      'emailError' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_email_error'), 'quizuint'))),
      'totalScore' => nl2br(quizu_run_string_template(esc_html__(get_option('quizu_settings_texts_total_score'), 'quizuint'))),
      'mediaTitle' => esc_html__('Select or upload a picture', 'quizuint'),
      'mediaText' => esc_html__('Use this picture', 'quizuint'),
      'flags' => array(
              'socialSharingFlag' => esc_html(get_option('quizu_settings_social_sharing_flag')),
              'userLoggedInFlag' => is_user_logged_in(),
              'isPreview' => is_preview(),
        )
    ));
  }

  add_action( 'wp_enqueue_scripts','quizu_front_enqueues', 10, 1);
}