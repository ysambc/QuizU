<?php 

defined('ABSPATH') or die("Cannot access pages directly.");

// DEFAULTS SETTINGS----------------------------------------------------------------------------------------------------------------

$master_list = multipq_master_config_list();

if (empty(get_option('mpq_settings_defaults_stored'))) {
  multipq_set_defaults($master_list);
  update_option('mpq_settings_defaults_stored', 'true');
}

if (is_array(array_intersect_key(wp_get_current_user()->roles, get_option('mpq_settings_permissions')))) {
  $current_user_is_capable = true;
}else{
  $current_user_is_capable = false;
}

// REGISTER POST TYPES----------------------------------------------------------------------------------------------------------------

function multipq_post_types_setup(){/*Define Quiz post type*/
    $args = array(
      'public' => true,
      'has_archive' => true,
      'show_in_menu' => 'mpq_main.php',
      'label'  => esc_html__('Branched Quizes', 'quizuint'),
      'supports' => array('editor', 'title'),
      'capability_type' => 'multipq',
      'map_meta_cap' => true
    );
    register_post_type( 'mpq_quiz', $args );
}
add_action( 'init', 'multipq_post_types_setup' );


// INTERNATIONALIZATION----------------------------------------------------------------------------------------------------------------

load_plugin_textdomain('quizuint', false, dirname(plugin_basename(__FILE__ )) . '/languages');


// REGISTER QUIZES WIDGET----------------------------------------------------------------------------------------------------------------

// register Foo_Widget widget
function register_mpq_widget() {
    register_widget( 'mpq_Widget' );
}
add_action( 'widgets_init', 'register_mpq_widget' );

/**
 * Adds mpq widget.
 */
class mpq_Widget extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  function __construct() {
    parent::__construct(
      'mpq_widget', // Base ID
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
    return multipq_master_config_list($list);
  }

  public function widget( $args, $instance ) {

    $mpq_login_required_flag = false;

    if (  
        (
            get_option('mpq_settings_user_login_flag') == 'true'
        ||  get_post_meta(multipq_find_linked_quiz(), '_mpq_user_login_flag', true) == 'true'
        )

          &&  !is_user_logged_in()
      ) 
    {
      $mpq_login_required_flag = true;
    }

    if (!$mpq_login_required_flag) {
      echo $args['before_widget'];
    }

    if ( ! empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    }
    $mpq_in_sidebar = true;
    include( plugin_dir_path( __FILE__ ) . 'views/shortcode.php');
    if (!$mpq_login_required_flag) {
      echo $args['after_widget'];
    }
    unset($mpq_in_sidebar);

  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {/*Input for widget title*/
    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    ?>
    <p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'quizuint' ); ?></label> 
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>
    <?php 
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {/*Update title*/
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

    return $instance;
  }

} // class Foo_Widget



// ADMIN COMPONENTS----------------------------------------------------------------------------------------------------------------

if (is_user_logged_in() && $current_user_is_capable) {

  // ENQUE AND LOCALIZE ADMIN SCRIPTS AND STYLES----------------------------------------------------------------------------------------------------------------

  function multipq_admin_enqueues($hook) {/*Enqueues for admin*/

    if ($hook != ('mpq_quiz' || 'mpq_settings') && get_post_type() != 'mpq_quiz') {
      return;
    }

    wp_enqueue_style('multipq_admin_css', plugins_url( '/includes/css/multipq_admin.css', __FILE__ ));
    wp_enqueue_style('fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style( 'wp-color-picker');

    wp_enqueue_script('multipq_admin_js', plugins_url( '/includes/js/multipq_admin.js', __FILE__ ), array('jquery'), null, true);
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('wp-color-picker');

    wp_enqueue_media();

    wp_localize_script( 'multipq_admin_js', 'mpqObj', array(
      'overlap' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_overlap')))),
      'minimal' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_minimal')))),
      'integer' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_integer')))),
      'option' => esc_html__('Option', 'quizuint'),
      'flags' => array(
        'autosave' => esc_html(get_option('mpq_settings_autosave_quiz_flag')),
      ),
    ));
  }

  add_action('admin_enqueue_scripts','multipq_admin_enqueues');

  // REGISTER CUSTOM COLUMNS----------------------------------------------------------------------------------------------------------------

  // Add the custom columns to the book post type:

    function mpq_quiz_list_columns($columns) {
        $columns['mpq_shortcode'] = __( 'Shortcode', 'quizuint' );
        return $columns;
    }

    add_filter( 'manage_mpq_quiz_posts_columns' , 'mpq_quiz_list_columns', 10, 2 );

    // Add the data to the custom columns for the book post type:

    function mpq_quiz_list_shortcode($column, $id){

      switch ($column) {
        case 'mpq_shortcode':
            echo '<input type="text" value="[mpq id='.'&#34;'.$id.'&#34;'.']" size="15" readonly onclick="this.select()">';
          break;

      }
    }

    add_action( 'manage_mpq_quiz_posts_custom_column' , 'mpq_quiz_list_shortcode', 10, 2 );


  // POST CLONE-----------------------------------------------------------------------------------------------------------------

  function mpq_archive_clone_quiz(){
   if (is_post_type_archive('mpq_quiz') && isset($_GET['mpq_settings']['mpq_clone'])) {
      $stripedvars = $_GET['mpq_settings'];

      array_walk_recursive($stripedvars, 'multipq_sanitize_deep');

      $current_post = get_post($stripedvars['mpq_clone']);

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
          wp_redirect( admin_url( 'edit.php?&post_type=mpq_quiz') );
          exit;
        } else {
          wp_die('Post creation failed, could not find original post: ' . $current_post_id);
        }

    }
  }

  add_action( 'pre_get_posts', 'mpq_archive_clone_quiz');

  function mpq_duplicate_quiz_link( $actions, $post ) {
    if (current_user_can('edit_posts')) {
      $actions['duplicate'] = '<a href="edit.php?&post_type=mpq_quiz&mpq_settings%5Bmpq_clone%5D='.$post->ID.'">'.esc_html__('Clone', 'quizuint').'</a>';
    }
    return $actions;
  }
   
  add_filter( 'post_row_actions', 'mpq_duplicate_quiz_link', 10, 2 );


  // SETUP mpq POST BEHAVIOUR-----------------------------------------------------------------------------------------------------------------

  function disable_mce_buttons( $opt ) {
      //set button that will be show in row 1
      $opt['theme_advanced_buttons1'] = 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,wp_more,|,spellchecker,wp_fullscreen,wp_adv,separator';
      return $opt;
  }

  add_filter('tiny_mce_before_init', 'disable_mce_buttons');

  
  // QUIZ PREVIEW-----------------------------------------------------------------------------------------------------------------

  function multipq_quiz_preview($content){
    if (get_post_type() == 'mpq_quiz') {
      remove_filter('the_content', 'multipq_quiz_preview');
      $content = do_shortcode('[mpq id="'.get_the_id().'"]');
      return $content;
    }
  }
  add_filter('the_content', 'multipq_quiz_preview');

  // SETUP mpq POST BEHAVIOUR-----------------------------------------------------------------------------------------------------------------

  function multipq_quiz_save_behaviour() {/*Define Quiz on save behaviour*/

    if (get_post_type() == 'mpq_quiz') {/*Check if target is empty*/

      if(isset($_POST[ 'multipq_quiz_save_behaviour' ]) && !wp_verify_nonce( $_POST[ 'multipq_quiz_save_behaviour' ], basename( __FILE__ ) )){
        return;
      };

      $all_questions = $_POST['mpq_all_questions'];

      $all_results = $_POST['mpq_all_results'];

      $all_flags = array(
          'user_login_flag',
          'show_scores_flag',
          'result_criteria_flag',
      );

      foreach ($all_flags as $flag) {
        update_post_meta(get_the_id(), '_mpq_'.$flag, $_POST['mpq_all_flags'][$flag] == 'on' ? 'true' : $_POST['mpq_all_flags'][$flag]);
      }

      array_walk_recursive($all_questions, "multipq_sanitize_deep");/*Sanitize array with htmlspecialchars*/
      array_walk_recursive($all_results, "multipq_sanitize_deep");/*Sanitize array with htmlspecialchars*/

      $all_questions_R = array_reverse($all_questions);/*Reverse array from display order*/

      foreach ($all_questions_R as $path) {
        $questionsR = array_reverse($path['questions']);/*Reverse array from display order*/
        $all_questions_R[$path['id']]['questions'] = $questionsR;/*Update questions*/
        foreach ($questionsR as $question) {

          if (!empty($question['essay_flag']) && $question['essay_flag'] == 'on') {
            $all_questions_R[$path['id']]['questions'][$question['id']]['essay_flag'] = 'true';/*Update options*/
          }else{
            $all_questions_R[$path['id']]['questions'][$question['id']]['essay_flag'] = 'false';/*Update options*/
          }
          
          if (!empty($question['multiple_choice_flag']) && $question['multiple_choice_flag'] == 'on') {
            $all_questions_R[$path['id']]['questions'][$question['id']]['multiple_choice_flag'] = 'true';/*Update options*/
          }else{
            $all_questions_R[$path['id']]['questions'][$question['id']]['multiple_choice_flag'] = 'false';/*Update options*/
          }

          $options = $question['options'];/*Reverse array from display order*/

          foreach ($options as $option) {
            $split = explode('||', $option['link']);
            $option['link'] = array();
            $option['link']['linkid'] = $split[0];
            $option['link']['linkpath'] = $split[1];

            if (!empty($option['essay'])) {
              foreach ($option['essay'] as $essay) {
                $split2 = explode('||', $essay['link']);
                $essay['link'] = array();
                $essay['link']['linkid'] = $split2[0];
                $essay['link']['linkpath'] = $split2[1];

                $option['essay'][$essay['id']] = $essay;
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

      update_post_meta(get_the_id(), '_mpq_questions', $all_questions_R);/*Insert updated questions*/
      update_post_meta(get_the_id(), '_mpq_results', $all_results);/*Insert updated results*/

      if (empty(get_post_meta(get_the_id(), '_mpq_questions', true))) {/*Check if target is empty*/
        update_post_meta( get_the_id(), '_mpq_questions', array());/*If empty, fill with an empty array*/
      }

      if (empty(get_post_meta(get_the_id(), '_mpq_results', true))) {/*Check if target is empty*/
        update_post_meta( get_the_id(), '_mpq_results', array());/*If empty, fill with an empty array*/
      }
    }

    if (get_post_type() == ('post' || 'page')) {/*If regular post or page*/

      $mpq_linked_quiz = htmlspecialchars((int)$_POST['mpq_linked_quiz']);/*Get linked quiz ID*/

      update_post_meta(get_the_id(), '_mpq_linked_quiz', $mpq_linked_quiz);/*Store linked quiz ID*/
    }
  }
  add_action( 'save_post', 'multipq_quiz_save_behaviour' );

  // REGISTER ADMIN META-BOXES----------------------------------------------------------------------------------------------------------------

  function multipq_questions_metabox(){/*Register Quiz config admin panel meta-boxes*/
    add_meta_box( 'multipq_questions', esc_html__('Branched Quiz', 'quizuint') , 'multipq_questions_metabox_content', 'mpq_quiz', 'normal' , 'high');
    add_meta_box( 'multipq_quizes', esc_html__('QuizU Linked Quiz', 'quizuint') , 'multipq_quiz_link_metabox_content', array('post', 'page'), 'side' , 'high');
  }
  add_action ( 'add_meta_boxes' , 'multipq_questions_metabox' ) ;

  function multipq_questions_metabox_content(){/*Define quiz config admin panel contents*/
    wp_nonce_field( basename( __FILE__ ), 'multipq_quiz_save_behaviour' );
    include( plugin_dir_path( __FILE__ ) . 'views/admin.php');
  }

  function multipq_quiz_link_metabox_content(){/*Define quiz config admin panel contents*/
    wp_nonce_field( basename( __FILE__ ), 'multipq_quiz_save_behaviour' );
    include( plugin_dir_path( __FILE__ ) . 'views/linker.php');
  }

  // REGISTER ADMIN PAGE----------------------------------------------------------------------------------------------------------------


  function mpq_settings_menu() {

    add_menu_page(
      'Branched Quizzes',
      'QuizU',
      'mpq_edit_quizzes',
      'mpq_main.php',
      NULL,
      'dashicons-tickets',
      6
    );

    add_submenu_page(
        'mpq_main.php',
        esc_html__('Add New Branched Quiz', 'quizuint'), /*page title*/
        esc_html__('New Quiz', 'quizuint'), /*menu title*/
        'mpq_edit_quizzes', /*roles and capabiliyt needed*/
        'post-new.php?post_type=mpq_quiz',
        '' /*replace with your own function*/
    );

    add_submenu_page(
        'mpq_main.php',
        esc_html__('QuizU Settings', 'quizuint'), /*page title*/
        esc_html__('Settings', 'quizuint'), /*menu title*/
        'mpq_edit_quizzes', /*roles and capabiliyt needed*/
        'mpq_settings',
        'mpq_settings_page_content' /*replace with your own function*/
    );

  }

  function mpq_settings_page_content(){
    include( plugin_dir_path( __FILE__ ) . 'views/settings.php');
  }

  add_action( 'admin_menu', 'mpq_settings_menu' );


  // REGISTER QUIZES SHORTCODE----------------------------------------------------------------------------------------------------------------

  function multipq_quiz_shortcode($atts) {/*Define and register front end Quiz shortcode*/
    $mpq_atts = shortcode_atts(array(
      'id' => '',
    ), $atts );

    $linked_quiz = $mpq_atts['id'];

    ob_start();
    include(plugin_dir_path( __FILE__ ) . 'views/shortcode.php');
    $output = ob_get_contents();
    ob_end_clean();

    return $output;

  }
  add_shortcode( 'mpq', 'multipq_quiz_shortcode');

}


// FRONT-END COMPONENTS----------------------------------------------------------------------------------------------------------------

if (!is_admin()) {

  // REGISTER AND ENQUEUE FRONT SCRIPTS AND STYLES----------------------------------------------------------------------------------------------------------------

  function multipq_front_enqueues($linked_quiz) {/*Enqueues for front*/
    
    wp_enqueue_script('multipq_front_js', plugins_url( '/includes/js/multipq_front.js', __FILE__ ), array('jquery'), null, true);
    wp_enqueue_script('jssocials', 'https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js', array('jquery'), null, true);
    wp_enqueue_style('multipq_front_css', plugins_url( '/includes/css/multipq_front.css', __FILE__ ));
    wp_enqueue_style('jsscoials-flat', 'https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials-theme-flat.css');

    $mpq_current_user = wp_get_current_user();
    $linked_quiz = multipq_find_linked_quiz();

    wp_localize_script( 'multipq_front_js', 'mpqObj', array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'defaultColor' => esc_html(get_option('mpq_settings_default_color')),
      'reset' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_reset'), 'quizuint'))),
      'next' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_next'), 'quizuint'))),
      'option' => esc_html__('Option', 'quizuint'),
      'optionEssay' => esc_html__('Essay op.', 'quizuint'),
      'essayError' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_essay_error'), 'quizuint'))),
      'checkedError' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_checked_error'), 'quizuint'))),
      'error' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_error'), 'quizuint'))),
      'share' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_share'), 'quizuint'))),
      'email' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_email'), 'quizuint'))),
      'send' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_send'), 'quizuint'))),
      'senderEmail' => esc_html(get_option('mpq_settings_email_address')),
      'senderName' => esc_html(get_option('mpq_settings_email_name')),
      'userEmail' => sanitize_email($mpq_current_user->user_email),
      'emailSubject' => htmlspecialchars_decode(multipq_run_string_template(get_option('mpq_settings_email_subject'))),
      'postEmail' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_post_email'), 'quizuint'))),
      'emailError' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_email_error'), 'quizuint'))),
      'totalScore' => nl2br(multipq_run_string_template(esc_html__(get_option('mpq_settings_texts_total_score'), 'quizuint'))),
      'mediaTitle' => esc_html__('Select or upload a picture', 'quizuint'),
      'mediaText' => esc_html__('Use this picture', 'quizuint'),
      'flags' => array(
              'socialSharingFlag' => esc_html(get_option('mpq_settings_social_sharing_flag')),
              'userLoggedInFlag' => is_user_logged_in(),
              'isPreview' => is_preview(),
        )
    ));
  }

  add_action( 'wp_enqueue_scripts','multipq_front_enqueues', 10, 1);
}