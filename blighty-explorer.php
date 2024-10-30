<?php
/**
 * Plugin Name: Blighty Explorer
 * Plugin URI: http://blighty.net/wordpress-blighty-explorer-plugin/
 * Description: Provides an easy integrateable layer between a folder hierarchy 
 * on Dropbox and the website. The folder tree can be navigated and files downloaded.
 * Changes to the original Dropbox folder are reflected through to the website. It also
 * provides functionality to allow for uploads to a Dropbox folder.
 * (C) 2015-2024 Chris Murfin (Blighty)
 * Version: 2.3.0
 * Author: Blighty
 * Text Domain: blighty-explorer
 * Author URI: http://blighty.net
 * License: GPLv3 or later
 **/

/**

Copyright (C) 2015-2024 Chris Murfin

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**/

defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

define('BEX_PLUGIN_NAME', 'Blighty Explorer');
define('BEX_PLUGIN_VERSION', '2.3.0');

define('BEX_UPLOADS_FOLDER', '_bex_uploads');

define('BEX_ANONYMOUS', 'All/Anonymous');
define('BEX_ADMIN', 'Administrator');

define('BEX_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));

require_once(BEX_PLUGIN_DIR .'/includes/DropboxClient2.php');
require_once(BEX_PLUGIN_DIR .'/folder.php');
require_once(BEX_PLUGIN_DIR .'/upload.php');
require_once(BEX_PLUGIN_DIR .'/utilities.php');

$dropbox = new DropboxClient2(array(
    'app_key' => 'ktms6mtlygelqeg',
    'app_secret' => 'wvl0ll46s2vz9pf',
    'app_full_access' => false,
    ),'en');

if ( is_admin() ){ // admin actions
	require_once(BEX_PLUGIN_DIR .'/admin-settings.php');
	add_action( 'admin_menu', 'bex_setup_menu' );
	add_action( 'admin_notices', 'bex_plugin_prequesites' );
	add_action( 'admin_init', 'bex_init' );
}

function bex_enqueue_stuff() {
    if (get_option('bex_suppress_css') == '2') {
        wp_enqueue_style( 'bex', plugins_url('stylemin.css', __FILE__), null, BEX_PLUGIN_VERSION );
    } elseif (get_option('bex_suppress_css') != '1') {
        wp_enqueue_style( 'bex', plugins_url('style.css', __FILE__), null, BEX_PLUGIN_VERSION );
    }
    wp_enqueue_script( 'jqueryform', includes_url('/js/jquery/jquery.form.js'), array('jquery') );
    wp_enqueue_script( 'bex-upload', plugins_url( 'js/bex.upload.js', __FILE__ ), null, BEX_PLUGIN_VERSION, true );
}

function bex_load_textdomain() {
    $domain = 'blighty-explorer';
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    load_textdomain( 'blighty-explorer', WP_LANG_DIR.'/blighty-explorer/'.$domain.'-'.$locale.'.mo');
    load_plugin_textdomain( 'blighty-explorer', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

add_action( 'wp_enqueue_scripts', 'bex_enqueue_stuff' );
add_action( 'wp_ajax_nopriv_submit_content', 'bex_submission_processor_nopriv' );
add_action( 'wp_ajax_submit_content', 'bex_submission_processor' );
add_action( 'plugins_loaded', 'bex_load_textdomain' );

add_shortcode( 'bex_folder', 'bex_folder' );
add_shortcode( 'bex_upload', 'bex_upload' );

if (!empty($_GET['file'])) {
  	add_action( 'template_include', 'bex_template' );
}

function bex_template() {
	return BEX_PLUGIN_DIR .'/blank.php';
}

?>