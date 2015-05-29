<?php
/*
Plugin Name: Shared Network Menus
Plugin URI: https://github.com/neptuneweb/wp-shared-network-menus
Description: Allows for sharing multiple menus from the main site of the network to the sub sites
Version: 0.2
Author: Stephen Cotton (original concept from Ron Rennick)
Author URI: http://www.neptuneweb.com/


*/
/* Copyright:   (C) 2014 Neptune Web, All rights reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


class NetworkSharedMenus {

	public static $class_name = 'NetworkSharedMenus';

	/**
	 * Store the Menu Locations
	 * @var array
	 */
	public $network_menu_slots = array();

	/**
	 * Singleton Instance of this Class
	 * @var nwdb
	 */
	private static $_instance;

	/**
	 * Singleton Function for this Class
	 * @return NetworkSharedMenus             Singleton Instance of this Class
	 */
	public static function instance(){
		if( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
    }

    public function __construct(){
    	/**
    	 * Hook the Filter for the Nav Menu Items
    	 */
		add_filter( 'wp_nav_menu_objects',         array( &$this, 'filter_nav_items' ), 10, 2 );
		/**
		 * Hook the Filter for gathering the Menu Locations to share
		 */
		add_filter(	'network_shared_menus_theme_locations', array( &$this, 'fetch_menu_slots' ), 10 ,1 );

		if( is_main_site() ){
			/**
			 * Hook into the Save Post to detect when a nav item has been saved
			 */
			add_action( 'save_post',  array( &$this, 'nav_menu_flush' ), 10, 2 );
			/**
			 * Create the Admin Screen for selecting the Menus
			 */
			add_action(	'admin_menu', array( &$this, 'create_admin_screen' ), 10 );
		}
    }

    /** Handy Logging function. */
    private function log($message){
    	if( WP_DEBUG ){
    		error_log( self::$class_name . ' :: ' . $message );
    	}
    }

    public function filter_nav_items( $content, $args ){
    	// Apply Filter to gather up additional Menu Locations to share
		$this->network_menu_slots = apply_filters( 'network_shared_menus_theme_locations', $this->network_menu_slots );
		$this->log('Slots: '.print_r($this->network_menu_slots,true));

		if( count( $this->network_menu_slots ) > 0 ){
			if( ! is_main_site() ){
				
				/** If the Theme Location is not set or is empty, return original items */
				if( ! isset( $args->theme_location) || strlen(trim($args->theme_location)) == 0 ){
					$this->log('No Theme Location Set, returning original menu items');
					return $content;
				}

				/** If the Theme Location is set, but is not one of the slots, return the original items */
				elseif ( isset($args->theme_location) && ! in_array($args->theme_location, $this->network_menu_slots) ){
					$this->log('Theme Location is Set ('.$args->theme_location.'), but is not one of the locations to be replaced');
					return $content;
				}

				/** If the Theme Location is set, but there is no cached object for that location, return the original items*/
				elseif ( isset($args->theme_location) && get_site_option( 'network_shared_menu_'.$args->theme_location, false ) === false ){
					$this->log('Theme Location is Set ('.$args->theme_location.'), but the menu is not cached in network_shared_menu_'.$args->theme_location.' ('.get_site_option('network_shared_menu_'.$args->theme_location ).')');
					return $content;
				}

				$network_menu = get_site_option( 'network_shared_menu_'.$args->theme_location );
				if( ! empty( $network_menu ) ){
					$this->log('Found Site Option for cached menu');
					$content = $network_menu;
				}
				
			/** Main Site and Not Cached */
			} elseif( ! get_option('network_shared_menu_'.$args->theme_location.'_cached') || strlen(trim(get_option('network_shared_menu_'.$args->theme_location.'_cached'))) == 0 ){
				$this->log( 'Caching Menu network_shared_menu_'.$args->theme_location );
				update_option( 'network_shared_menu_'.$args->theme_location.'_cached', '1' );
				update_site_option( 'network_shared_menu_'.$args->theme_location, $content );
			}
		}
		
		return $content;
    }

    /** Whenever a nav item is saved, mark the menus for caching */
    public function nav_menu_flush( $post_id, $post ){
    	if( $post->post_type == 'nav_menu_item' ){
			$this->network_menu_slots = apply_filters( 'network_shared_menus_theme_locations', $this->network_menu_slots );
			$this->log('Slots: '.print_r($network_menu_slots,true));
			foreach( $this->network_menu_slots as $a_slot ){
				update_option( 'network_shared_menu_'.$a_slot.'_cached', '' );	
			}		
		}
    }

    /** Register a menu on the main site */
    public function create_admin_screen(){
		add_theme_page( 'Network Menus', 'Network Menus', 'edit_themes', basename(__FILE__), array(&$this, 'admin_screen') );	
    }

    /** Create the screen that allows main site admins to select the theme locations to cache/share */
    public function admin_screen(){
    	if( $_POST && $_POST['network_shared_menu_settings'] == 1 ){
			$save_network_menus = array();
			if( count( $_POST['network_shared_menus']) > 0 ){
				foreach( $_POST['network_shared_menus'] as $a_theme_location ){
					$save_network_menus[] = $a_theme_location;
				}
			}
			update_site_option( 'network_shared_menus', $save_network_menus );
			foreach( $save_network_menus as $a_slot ){
				update_option( 'network_shared_menu_'.$a_slot.'_cached', '' );	
			}	
		}
		$current_network_menus = get_site_option('network_shared_menus', array());
		?>
		<div class="wrap">
			<h2>
				Network Menus
			</h2>
			<p><?php echo _e('Select the Theme Locations from the currently selected theme that you wish to share across the network.'); ?></p>
			<form method="post" action="">
				<input type="hidden" name="network_shared_menu_settings" value="1" />
				<ul>
					<?php foreach( get_registered_nav_menus() as $theme_location => $a_registered_nav_menu ) : ?>
					<li>
						<label for="network_shared_menus_<?php echo $theme_location; ?>">
							<input type="checkbox" name="network_shared_menus[]" value="<?php echo $theme_location; ?>" id="network_shared_menus_<?php echo $theme_location; ?>" <?php echo ( in_array($theme_location, $current_network_menus)) ? 'checked="checked"' : ''; ?>/>
							<?php echo $a_registered_nav_menu; ?>
						</label>
					</li>
					<?php endforeach; ?>
				</ul>
				<input class="button-primary" type="submit" name="Save" value="<?php _e("Save"); ?>" id="submitbutton" />
			</form>
		</div>
		<?
    }

    /** Hooks into the filter to specify the menu locations to share */
    public function fetch_menu_slots( $slots ){
    	$current_network_menus = get_site_option('network_shared_menus', array());
		if( count($current_network_menus) > 0 ){
			$slots = array_merge($slots, $current_network_menus);
		}
		return $slots;
    }
}

NetworkSharedMenus::instance();
