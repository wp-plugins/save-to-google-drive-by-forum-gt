<?php 
/*
	Plugin Name: Wordpress Save To Google Drive
	Plugin URI: http://andreapernici.com/wordpress/save-to-drive/
	Description: Add Save To Google Drive Button to Wordpress.
	Version: 1.0.1
	Author: Andrea Pernici
	Author URI: http://www.andreapernici.com/
	
	Copyright 2013 Andrea Pernici (andreapernici@gmail.com)
	
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

define( 'SAVETODRIVE_VERSION', '1.0.1' );

$pluginurl = plugin_dir_url(__FILE__);
if ( preg_match( '/^https/', $pluginurl ) && !preg_match( '/^https/', get_bloginfo('url') ) )
	$pluginurl = preg_replace( '/^https/', 'http', $pluginurl );
define( 'SAVETODRIVE_FRONT_URL', $pluginurl );

define( 'SAVETODRIVE_URL', plugin_dir_url(__FILE__) );
define( 'SAVETODRIVE_PATH', plugin_dir_path(__FILE__) );
define( 'SAVETODRIVE_BASENAME', plugin_basename( __FILE__ ) );

if (!class_exists("AndreaSaveToDrive")) {

	class AndreaSaveToDrive {
		/**
		 * Class Constructor
		 */
		function AndreaSaveToDrive(){
		
		}
		
		/**
		 * Enabled the AndreaSaveToDrive plugin with registering all required hooks
		 */
		function Enable() {
			
			add_action('admin_menu', array("AndreaSaveToDrive",'SaveToDriveMenu'));
			add_action('wp_head', array("AndreaSaveToDrive","SaveToDriveInit"));
			add_shortcode('apstd', array("AndreaSaveToDrive",'andrea_save_to_drive_shortcode_handler'));
			add_shortcode('apstd-a', array("AndreaSaveToDrive",'andrea_save_to_drive_shortcode_handler'));
			add_filter( 'widget_text', 'shortcode_unautop');
			add_filter( 'widget_text', 'do_shortcode');			
		}
		
		/**
		 * Set the Admin editor to set options
		 */
		 
		function SetAdminConfiguration() {
			add_action('admin_menu', array("AndreaSaveToDrive",'SaveToDriveMenu'));
			return true;
		}
		
		function SaveToDriveInit() {
			
			$googlestd_lang = get_option( 'save_to_drive_lang' );
			$googlestd_js = get_option( 'save_to_drive_js' );
			
			if ($googlestd_js!=1){
				echo '<script type="text/javascript" src="http://apis.google.com/js/plusone.js">';
				if ($googlestd_lang!='') {echo "{lang: '".$googlestd_lang."'}";}
				echo $googlestd_js;
				echo '</script>';
			}
		}
		
		function SaveToDriveMenu() {
			add_options_page('Save To Drive Options', 'Save To Drive', 'manage_options', 'save-to-drive-options', array("AndreaSaveToDrive",'SaveToDriveOptions'));
		}
		
		function SaveToDriveOptions() {
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
		    // variables for the field and option names 
		    $save_to_drive_js = 'save_to_drive_js';
		    $save_to_drive_lang = 'save_to_drive_lang';
		    
		    $hidden_field_name = 'mt_submit_hidden';
		    $data_field_name_js = 'save_to_drive_js';
			$data_field_comments_lang = 'save_to_drive_lang';
		
		    // Read in existing option value from database
		    $opt_val_js = get_option( $save_to_drive_js );
		    $opt_val_comments_lang = get_option( $save_to_drive_lang );
		    
		    // See if the user has posted us some information
		    // If they did, this hidden field will be set to 'Y'
		    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		        // Read their posted value
		        $opt_val_js = $_POST[ $data_field_name_js ];
		    	$opt_val_comments_lang = $_POST[ $data_field_comments_lang ];
		
		        // Save the posted value in the database
		        update_option( $save_to_drive_js, $opt_val_js );
		        update_option( $save_to_drive_lang, $opt_val_comments_lang );
		
		        // Put an settings updated message on the screen
		
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'menu-save-to-drive' ); ?></strong></p></div>
		<?php
		
		    }
		    // Now display the settings editing screen
		    echo '<div class="wrap">';
		    // header
		    echo "<h2>" . __( 'Save To Drive Options', 'menu-save-to-drive' ) . "</h2>";
		    // settings form
		    
		    ?>
		
		<form name="form1" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		
		<?php $options_js = get_option( 'save_to_drive_js' ); ?>
		<p><?php _e("Exclude Js:", 'menu-save-to-drive' ); ?> 
		<input type="checkbox" name="save_to_drive_js" value="1"<?php checked( 1 == $options_js ); ?> /> (if you want to put by hand or you already use +1 js)</p>
		
		<?php $options_lang = get_option( 'save_to_drive_lang' ); ?>
		<p><?php _e("Language:", 'menu-save-to-drive' ); ?> 
		<input type="text" name="save_to_drive_lang" value="<?php echo $options_lang; ?>" /> (default blank is en, you can put it for italian.)</p>

		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>
		
		</form>
		<?php echo "<h2>" . __( 'Put Function in Your Theme', 'menu-save-to-drive' ) . "</h2>"; ?>
		<p>If you want to put the box anywhere in your theme or you have problem showing the box simply use this function:</p>
		<p>if (function_exists('andrea_save_to_drive')) { andrea_save_to_drive(); }</p>
		</div>
		
		<?php

		}	
		
		/**
		 * Handler for AndreaSaveToDrive Widget Shortcode
		 * $atts    ::= array of attributes
   		 * $content ::= text within enclosing form of shortcode element
   		 * $code    ::= the shortcode found, when == callback name
		 * examples: [my-shortcode]
		 *           [my-shortcode/]
   		 *			 [my-shortcode foo='bar']
         *           [my-shortcode foo='bar'/]
         *           [my-shortcode]content[/my-shortcode]
         *           [my-shortcode foo='bar']content[/my-shortcode]
         *           <script src="https://apis.google.com/js/plusone.js"></script>
         *           <div class="g-savetodrive"
         *            data-filename="My Statement.pdf"
         *            data-sitename="My Company Name"
         *            data-src="/path/to/myfile.pdf">
         *           </div>
		 */
		 
		function andrea_save_to_drive_shortcode_handler($atts, $content=null, $code="") {
			
			$ifstart = '<div class="g-savetodrive" ';
			$ifend = '</div>';
			
			switch ($code) {
/* <div class="g-savetodrive" data-filename="My Statement.pdf" data-sitename="My Company Name" data-src="/path/to/myfile.pdf"></div> */
				case 'a':
					extract( shortcode_atts( array(  
					   'datafilename' => 'My Statement.pdf', 
					   'datasitename' => 'My Company Name',
					   'datasrc' => '/path/to/myfile.pdf',
					), $atts ) );

					return $ifstart.'data-filename="'.$datafilename.'" data-sitename="'.$datasitename.'" data-src="'.$datasrc.'">'.$ifend;
					break;
				default:
					extract( shortcode_atts( array(  
					   'datafilename' => 'My Statement.pdf', 
					   'datasitename' => 'My Company Name',
					   'datasrc' => '/path/to/myfile.pdf',
					), $atts ) );

					return $ifstart.'data-filename="'.$datafilename.'" data-sitename="'.$datasitename.'" data-src="'.$datasrc.'">'.$ifend;
					break;

			}

		}
		
		/**
		 * Returns the plugin version
		 *
		 * Uses the WP API to get the meta data from the top of this file (comment)
		 *
		 * @return string The version like 1.0.0
		 */
		function GetVersion() {
			if(!function_exists('get_plugin_data')) {
				if(file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) require_once(ABSPATH . 'wp-admin/includes/plugin.php'); //2.3+
				else if(file_exists(ABSPATH . 'wp-admin/admin-functions.php')) require_once(ABSPATH . 'wp-admin/admin-functions.php'); //2.1
				else return "0.ERROR";
			}
			$data = get_plugin_data(__FILE__);
			return $data['Version'];
		}
	
	}
}

/*
 * Plugin activation
 */
 
if (class_exists("AndreaSaveToDrive")) {
	$afs = new AndreaSaveToDrive();
}


if (isset($afs)) {
	
	add_action("init",array("AndreaSaveToDrive","Enable"),1000,0);

}

if (!function_exists('andrea_save_to_drive')) {
	function andrea_save_to_drive() {
		$save_to_drive = new AndreaSaveToDrive();
		return $save_to_drive->SetSaveToDriveCode();
	}	
}
