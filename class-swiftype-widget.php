<?php
class Swiftype_Search_Widget extends WP_Widget {

  function __construct() {
    $widget_ops = array('classname' => 'swiftype_search_widget', 'description' => __( "A Swiftype search form for your site") );
    parent::__construct('search', __('Swiftype Search'), $widget_ops);
  }

  function widget( $args, $instance ) {
    extract($args);
    $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

    echo $before_widget;
    if ( $title ) {
      echo $before_title . $title . $after_title;
    }

    // Use current theme search form if it exists
    get_search_form();

    echo $after_widget;
  }

  function form( $instance ) {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'category' => 0) );
    $title = $instance['title'];
    $category = $instance['category'];
?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
        <input class="regular-text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
      </label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category ID:'); ?>
        <input class="small-text" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo esc_attr($category); ?>" />
      </label>
    </p>
<?php
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $new_instance = wp_parse_args((array) $new_instance, array( 'title' => '', 'category' => 0));
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['category'] = strip_tags($new_instance['category']);
    return $instance;
  }

}

add_action( 'widgets_init', create_function( '', 'register_widget( "swiftype_search_widget" );' ) );

?>