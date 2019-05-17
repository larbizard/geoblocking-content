<?php
/**
 * Plugin Name: Geoblocking Content
 * Plugin URI:  https://larbizard.com/geoblocking
 * Description: WordPress Plugin to block content access based on visitor geolocalisation
 * Version:     1.0.0
 * Author:      Gharib Larbi
 * Author URI:  https://larbizard.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: geoblocking-content
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Includes
 */


/*function add_city_geoblocking_to_post($post_id, $city){
	add_post_meta( $post_id, 'geoblocking_city', $city );
}*/

/**
 * Plugin options
 */

/**
 * Administration menu
 */

if( is_admin() ) {
	add_action( 'admin_menu', 'geoblocking_content_menu' );
	add_action( 'admin_init', 'register_mysettings' );	
} else {
	// non-admin enqueues, actions, and filters
}

function register_mysettings() {
	register_setting( 'country-option-group', 'blackout_ray');
}


function geoblocking_content_menu() {
	add_options_page('Geoblocking content options', 'Geoblocking content', 'manage_options', 'geoblocking-content-blackout', 'geoblocking_content_options');
}

include 'geoblocking-options.php';

/* Define the custom box */

// WP 3.0+
add_action( 'add_meta_boxes', 'post_options_metabox' );

// backwards compatible
add_action( 'admin_init', 'post_options_metabox', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'save_post_options' );

/**
 *  Adds a box to the main column on the Post edit screen
 * 
 */
function post_options_metabox() {
    add_meta_box( 'post_options', __( 'Geoblocking City Blackout' ), 'post_options_code', 'post', 'normal', 'high' );
}

/**
 *  Prints the box content
 */
function post_options_code( $post ) { 
    wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' );
    $meta_info = get_post_meta( $post->ID, '_meta_info', true) ? get_post_meta( $post->ID, '_meta_info', true) : 1; ?>
    <h2><?php _e( 'City to block' ); ?></h2>
    <div class="alignleft">
         <select name="_meta_info">
		  <option value="Montreal" <?php selected( $meta_info, "Montreal" ); ?> >Montreal</option>
		  <option value="Quebec" <?php selected( $meta_info, "Quebec" ); ?> >Quebec</option>
		  <option value="Victoriaville" <?php selected( $meta_info, "Victoriaville" ); ?> >Victoriaville</option>
		  <option value="Shawinigan" <?php selected( $meta_info, "Shawinigan" ); ?> >Shawinigan</option>
		</select>
    </div>

    <div class="alignright">
        <span class="description"><?php _e( 'Select the city you want to blackout' ); ?></span>
    </div>
    <div class="clear"></div>
    <hr /><?php
}

/** 
 * When the post is saved, saves our custom data 
 */
function save_post_options( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce( @$_POST[$_POST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Check permissions
  if ( !current_user_can( 'edit_post', $post_id ) )
     return;

  // OK, we're authenticated: we need to find and save the data
  if( 'post' == $_POST['post_type'] ) {
      if ( !current_user_can( 'edit_post', $post_id ) ) {
          return;
      } else {
          update_post_meta( $post_id, '_meta_info', $_POST['_meta_info'] );
      }
  } 

}

/* When loading the post check if the user is allowed to access */
add_action( 'the_post', 'geoblocking_blackout' );

function geoblocking_blackout($post){
	$user_ip_adresse = getUserIP();
	//TO DO Get a list of all cities and there lat and lon and check if the visitor position is within this perimeter based on the given ray
	//printf('<ul><li>Latitude: %s</li><li>Longitude %s</li><li>Ray %s</li></ul', json_decode(geolocate_ip($user_ip_adresse))->lat, json_decode(geolocate_ip($user_ip_adresse))->lon, get_option('blackout_ray'));
	if(get_post_meta( $post->ID, '_meta_info', true) == json_decode(geolocate_ip($user_ip_adresse))->city){
		add_action( 'the_content', 'change_content' );
	}
}


function change_content($content){
	return $content = '<h3>THE SCHEDULED PROGRAM IS NOT AVAILABLE IN YOUR REGION</h3>
					   <h4>FOR MORE INFORMATION INCLUDING PROGRAMING SCHEDULES contact@website.com</h4>
					   <img src="/wp-content/plugins/geoblocking-content/images/RCC_NHL_MAP_1800px-wide3.png" />';
}