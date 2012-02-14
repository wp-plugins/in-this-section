<?php
/*
Plugin Name: In This Section Widget
Plugin URI: 
Description: A widget showing pages 'in this section', according to a custom menu. Great for CMS.
Version: 1.0
Author: Tom Fletcher
Text-Domain: in-this-section

Copyright 2011  Tom Fletcher  (email : tf.hartle@gmail.com)

Released under the GPL license
http://www.opensource.org/licenses/gpl-license.php

**********************************************************************
This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
**********************************************************************
*/

// Disallow direct access to the plugin file
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

// load text domain
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'in-this-section', false, $plugin_dir );

// start the widget
class in_this_section extends WP_Widget {
	function in_this_section() {
		$widget_options = array(
			'classname' => 'in-this-section',
			'description' => __('Show links to other pages in the section, as defined in a custom menu', 'in-this-section')
		);
		$control_options = array(
			); // not currently used
		parent::__construct('in-this-section', __('In This Section', 'in-this-section'), $widget_options);
	}
	
	function widget( $args, $instance ) {
		$its_section_menu = its_section_menu( $instance['nav_menu'] );
		if( !$its_section_menu ) {
			$its_section_menu = false;
			return false;
		}
		
		echo $args['before_widget'];
		if ( $instance['title'] ) {
            echo $args['before_title'] . $instance['title'] . $args['after_title'];
        }

		echo $its_section_menu;

		echo $args['after_widget'];
    }
    
    function update( $new_instance, $old_instance ) {
    	$defaults = array(
    		'title' => __( 'In This Section', 'in-this-section' ),
    		'nav_menu' => ''
    	);
    	// add defaults to array
    	$new_instance = wp_parse_args( (array) $new_instance, $defaults );
    	
    	// make received values safe to save
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		$instance['nav_menu'] = (int) $new_instance['nav_menu'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
    		'title' => __( 'In This Section', 'in-this-section' ),
    		'nav_menu' => ''
    	);
    	// add defaults to array
    	$instance = wp_parse_args( (array) $instance, $defaults );
	
		$title = $instance['title'];
		$nav_menu = $instance['nav_menu'];

		// Get menus
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		// If no menus exists, direct the user to go and create some.
		if ( !$menus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.', 'in-this-section'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'in-this-section') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:', 'in-this-section'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
		<?php
			foreach ( $menus as $menu ) {
				$selected = $nav_menu == $menu->term_id ? ' selected="selected"' : '';
				echo '<option'. $selected .' value="'. $menu->term_id .'">'. $menu->name .'</option>';
			}
		?>
			</select>
		</p>
		<?php
    }
}

// register the widget
add_action('widgets_init', create_function('', 'return register_widget("in_this_section");'));


// core functions

/* Renders navigation links for the current section, from chosen custom menu
*
* @param $menu (int|string) The custom menu to use (id, name or slug)
* @return mixed echo list of links on success, returns false if no other pages in section or failure
*/
function its_section_menu( $menu ) {
	if( !is_nav_menu( $menu ) )
		return false;
	
	// get the nav menu
	$menu = wp_nav_menu( array( 'menu' => $menu, 'echo' => false ) );
	
	// mark the sub-menus
	$menu = str_replace( array( '<ul class="sub-menu">', '</ul>' ), array( '*%%sub-menu%%', '*' ), $menu );
	// make an array containing menu lists, main menu gets massacred, sub-menus remain intact in array entries
	$menu_arr = explode( '*', $menu );
	
	// find the key for the array entry with current-menu-item
	$current_item_key = its_search_in_array( 'current-menu', $menu_arr );	
	
	// check the array entry is a sub-menu
	if( its_in_string( '%%sub-menu%%', $menu_arr[$current_item_key] ) ) {
		$sub_menu = $menu_arr[$current_item_key];
	} else {
		// if it's not, we have the top-level menu
		// lets check the next array entry is the sub-menu we want...
		
		// if the next array entry is the sub-menu for this page, the 'current-menu-item' class will be attached to the last li item in the current array entry...
		// let's split the string into an array with two entries using 'current-menu-item'...
		// if the second array entry doesn't have a li item in it, we split the last li item and there is a sub-menu
		$top_level_arr = explode( 'current-menu-item', $menu_arr[$current_item_key] );
		if( its_in_string( 'li', $top_level_arr[1] ) ) {
			// there isn't a sub-menu for this menu item
			return '<p>'. __('No other pages in this section.', 'in-this-section').'</p>';
		} else {
			// there is a sub-menu
			$current_item_key++;
			$sub_menu = $menu_arr[$current_item_key];
		}
	}
	
	return '<ul class="menu section-menu">'.str_replace( '%%sub-menu%%', '', $sub_menu ).'</ul>';
}

/* Searches for a string within a string
* Returns true if found, false if not
*/
function its_in_string( $find, $input_string ) {
	// check inputs are strings
	if( !is_string( $find ) || !is_string( $input_string ) )
		return false;
	
	// find if $find is in string, return true if it is, rather than it's position
	if( strpos( $input_string, $find ) === FALSE ) {
		return false;
	} else {
		return true;
	}
}

/* Searches for a string with the values of an array
*
* Array must contain only contain strings, i.e not multidimensional
* 
* @param $find The string to search for in array values
* @param $input_array The array to search in
* @param $bool Run function in boolean mode, returns true if string is found
*
* @return mixed The array key the string occurs in. False if $find is not present, or $find is not a string, $input_array is not an array or does not contain only strings 
* Only returns the key for the first instance of $find
*/
function its_search_in_array( $find, $input_array, $bool = false ) {
	// check the inputs are a string and an array
	if( !is_array( $input_array ) || !is_string( $find ) )
		return false;
	
	$return_array = array();
	foreach( $input_array as $entry ) {
		// check the values in array are strings
		if( !is_string( $entry ) )
			return false;
		
		if( strpos( $entry, $find ) === FALSE ) {
			$return_array[] = false;
		} else {
			$return_array[] = true;
		}
	}
	
	$array_key = array_search( true, $return_array );
	
	// run function in boolean mode
	if( $bool ) {
		if( $array_key === FALSE ) {
			return false;
		} else {
			return true;
		}
	}
	
	return $array_key;
}

?>