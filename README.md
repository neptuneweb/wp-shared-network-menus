# Shared Network Menus
Easy to use shared network menus based on selectable theme locations.

Based on the work by Ron Rennick:

https://github.com/rrennick/network-wide-menu
http://wpmututorials.com/plugins/networkwide-menu/

This plugin is an adaptation of Ron's pattern, extended to store the menus based on their theme location, which removes the restriction of a single slot, or the first slot. An admin menu has been added to the main site in a network under Appearance > Network Menus.  Check off the menu's  you wish to share between sites and save.

<b>The same restriction applies in this plugin as did Ron's.  <u>You need to create and register menus in the relevant menu locations on the other sites for this to work.</u></b>

A filter has been added (admin menu uses this) to allow for themes or other plugins to add menus for syncing.

```php
add_filter('network_shared_menus_theme_locations', 'my_awesome_function', 10, 1);
functin my_awesome_function( $slots ){
  return array_merge( $slots, array( 'plugin-menu', 'other-menu-location' ) );
}
```

## NOTE:
This plugin was created to solve a problem encountered during a project.  In researching the issue, we recognized that a lot of people have run into this, and that Ron's solution was a wonderful proof of concept.  Because of the specific needs of the project, his work provided the best starting point to achieve our goals.  This plugin is being provided back to the community as-is, with absolutely, positively, no expectation of support, future development or feature enhancement.  While we would like to clean up the code and properly package it, there are no plans to do so at this moment.  If during the course of the project we identify any bugs or deficiencies, we will do our best to merge our updates back here for the benefit of all.  Feel free to use this to solve the same issue, or modify it to solve more!

### That being said...
Here is how this could be improved:

1. Only invalidate the cache flag for the menu that the nav_menu_item belongs to
2. Create the required placeholder menus on the sub sites automatically when sharing the menu for the first time.
3. Add an admin notification after the cache flag has been cleared to remind the administrators that the menus must be viewed on the main site to be cached for the sub sites.
4. 