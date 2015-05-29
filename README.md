# wp-shared-network-menus
Easy to use shared network menus based on selectable theme locations.

Based on the work by Ron Rennick:
https://github.com/rrennick/network-wide-menu
http://wpmututorials.com/plugins/networkwide-menu/

This plugin is an adaptation of Ron's pattern, extended to store the menus based on their theme location, which removes the restriction of a single slot, or the first slot. An admin menu has been added to the main site in a network under Appearance > Network Menus.  Check off the menu's  you wish to share between sites and save.

The same restriction applies in this plugin as did Ron's.  You have to have created and registered menus in the relevant menu locations on the other sites for this to work.

A filter has been added (admin menu uses this) to allow for themes or other plugins to add menus for syncing.

add_filter('ra_wp_nav_menu_filter_slots', 'my_awesome_function', 10, 1);
functin my_awesome_function( $slots ){
  return array_merge( $slots, array( 'plugin-menu', 'other-menu-location' ) );
}
