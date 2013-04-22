<?php

/**
	* The Swiftype Category Filtered Search Widget class
	*
	* This class provides a widget that filters searches to a specific category.
	*
	* @author  Quin Hoxie <qhoxie@swiftype.com>, Matt Riley <mriley@swiftype.com>
	*
	* @since 1.0
	*
	*/

class Swiftype_Search_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'swiftype_search_widget', 'description' => __( 'Search content in a specific category' ) );
		parent::__construct( 'swiftype_search_widget', __( 'Category Filtered Search' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$category = $instance['category'];

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		$form = '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/' ) ) . '" >
			<div><label class="screen-reader-text" for="s">' . __( 'Search for:' ) . '</label>
			<input type="text" value="' . get_search_query() . '" name="s" id="s" />
			<input type="hidden" value="' . $category . '" name="st-cat" id="st-cat" />
			<input type="submit" id="searchsubmit" value="'. esc_attr__( 'Search' ) .'" />
			</div>
			</form>';

		echo $form;

		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'category' => 0 ) );
		$title = $instance['title'];
		$category = $instance['category'];
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
				<input class="regular-text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category ID:' ); ?>
<?php
		$settings = array(
			'name' => $this->get_field_name( 'category' ),
			'id' => $this->get_field_id( 'category' ),
			'selected' => $category
		);
		wp_dropdown_categories( $settings );
?>
			</label>
		</p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'category' => 0 ) );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category'] = intval( $new_instance['category'] );
		return $instance;
	}

}

add_action( 'widgets_init', create_function( '', 'register_widget( "swiftype_search_widget" );' ) );
