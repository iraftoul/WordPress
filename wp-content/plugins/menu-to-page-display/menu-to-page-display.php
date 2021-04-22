<?php



/**



 * Plugin Name: Menu to Page Display



 * Plugin URI: http://www.widgetmedia.co/menu-to-page-display/



 * Description: Display menu in a page using the [menu-display] shortcode



 * Version: 1.0



 * Author: Paul Angell



 * Author URI: http://www.widgetmedia.co



 *



 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 



 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume 



 * that you can use any other version of the GPL.



 *



 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 



 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.



 *



 * @package Menu to Page Display



 * @version 1.0



 * @author Paul Angell



 * @copyright Copyright (c) 2014, Paul Angell



 * @link http://www.widgetmedia.co/menu-to-page-display/



 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html



 */



 



 



/**



 * To Customize, use the following filters:



 *



 * `menu_display_shortcode_args`



 * For customizing the $args passed to WP_Query



 *



 * `menu_display_shortcode_output`



 * For customizing the output of individual pages.



 * Example: https://github.com/RustyBadRobot/menu-to-page-display.git



 */ 



 

// Add stylesheet

function menu_display_scripts() {

	wp_enqueue_style( 'grid-load', plugins_url(). '/menu-to-page-display/assets/style.css' );

}


add_action( 'wp_enqueue_scripts', 'menu_display_scripts' );


// Create the exceprt word limit

function string_limit_menu_display_shortcode($string, $word_limit)

{

  $words = explode(' ', $string, ($word_limit + 1));

  if(count($words) > $word_limit)

  array_pop($words);

  return implode(' ', $words);

}


// Create the shortcode



add_shortcode( 'menu-display', 'be_menu_display_shortcode' );



function be_menu_display_shortcode( $atts ) {



	// Original Attributes, for filters



	$original_atts = $atts;




	// Pull in shortcode attributes and set defaults



	$atts = shortcode_atts( array(



		'menu_name'            => false,
	


		'date_format'         => 'F j, Y',



		'image_size'          => false,



		'include_content'     => false,



		'include_date'        => false,



		'include_excerpt'     => false,

		

		'column_count'		=> '2',
		
		
		'wrapper'             => 'div',

		

		'read_more'		      => false,



	), $atts );
	


	$menu_name = sanitize_text_field( $atts['menu_name'] );



	$date_format = sanitize_text_field( $atts['date_format'] );
	


	$image_size = sanitize_key( $atts['image_size'] );



	$include_content = (bool)$atts['include_content'];



	$include_date = (bool)$atts['include_date'];



	$include_excerpt = (bool)$atts['include_excerpt'];



	$column_count = sanitize_text_field( $atts['column_count'] );
	
	
	
	$wrapper = sanitize_text_field( $atts['wrapper'] );
	
	

	$read_more = (bool)$atts['read_more'];
	
	
	// Set up html elements used to wrap the posts. 

	// Default is ul/li, but can also be ol/li and div/div

	$wrapper_options = array( 'ul', 'ol', 'div' );

	if( ! in_array( $wrapper, $wrapper_options ) )

		$wrapper = 'ul';

	$inner_wrapper = 'div' == $wrapper ? 'div' : 'li';
	
	
	
	// This code based on wp_nav_menu's code to get Menu ID from menu slug

	//if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {
	if ( wp_get_nav_menu_object( $menu_name )) {
	
		$menu = wp_get_nav_menu_object( $menu_name );

		$menu_items = wp_get_nav_menu_items($menu->term_id);

		$output = '<div id="menu-' . $menu_name . '">';
		
		$inner = '';

		$i= 0 ;

		foreach ( (array) $menu_items as $key => $menu_item ) {
		
		if ( $i == $column_count )
		{
			$i= 1;
		} else {
			$i++;
		};
		
			$image = $date = $excerpt = $content = '';
		
			$id = get_post_meta( $menu_item->ID, '_menu_item_object_id', true );
						
			$thePost = get_post( $id );

			$title = '<h2><a href="' . get_permalink( $id ) . '">' . apply_filters( 'the_title', $thePost->post_title ) . '</a></h2>';
			
			if ( $image_size && has_post_thumbnail( $id ) )
			
				$image = '<a class="image" href="' . get_permalink( $id ) . '">' . get_the_post_thumbnail( $id, 'full' ) . '</a> ';
				
			if ( $include_date ) 

				$date = ' <span class="date">' . apply_filters( 'the_title', $thePost->post_date ) . '</span>';
			
			if ( $include_excerpt )
			
				$snippet = apply_filters( 'the_excerpt', $thePost->post_excerpt );
				$excerpt = ' <span class="excerpt">' . string_limit_display_page_shortcode($snippet,25) . '</span>';
			
			if( $include_content )
				$content = '<div class="content">' . apply_filters( 'the_content', $thePost->post_content ) . '</div>';
			
			if ( $read_more )
				$read_more_link = '<br /><a class="image" href="' . get_permalink( $id ) . '"><span class="read_more">Read More</span>'; 
			
		$class = array();	

		$class = apply_filters( 'menu_display_shortcode_post_class', $class );
		
		// Column Count

		if( empty( $column_count ) ) {
		
			$output = '<' . $inner_wrapper . ' class="no_column ' . implode( '', $class ) . '">' . $image . $title . $date . $excerpt . $content . $read_more_link . '</' . $inner_wrapper . '>';

		} elseif ( $i == $column_count ) {

			$output = '<' . $inner_wrapper . ' class="column column-1-' . $column_count . ' column-last">' . $image . $title . $date . $excerpt . $content . $read_more_link . '</' . $inner_wrapper . '><br class="column-clear" />';

		} else {

			$output = '<' . $inner_wrapper . ' class="column column-1-' . $column_count . '">' . $image . $title . $date . $excerpt . $content . $read_more_link . '</' . $inner_wrapper . '>';

		}
		
		// If post is set to private, only show to logged in users

		if( 'private' == get_post_status( $post->ID ) && !current_user_can( 'read_private_posts' ) )

			$output = '';
		
		$inner .= apply_filters( 'menu_display_shortcode_output', $output, $original_atts, $image, $title, $date, $excerpt, $inner_wrapper, $content, $class );
		
	}
		
    } else {
    
		$inner = '<ul><li>Menu "' . $menu_name . '" not defined.</li></ul>';
    }
    
	$open = apply_filters( 'menu_display_shortcode_wrapper_open', '<' . $wrapper . ' class="display-pages-listing">', $original_atts );

	$close = apply_filters( 'menu_display_shortcode_wrapper_close', '</' . $wrapper . '>', $original_atts );

	$return = $open . $inner . $close;


	return $return;

}