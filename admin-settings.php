<?php
/**

Copyright (C) 2015-2018 Chris Murfin (Blighty)

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

v2.2.1

**/

function bex_plugin_prequesites() {
	global $current_user;
	$userid = $current_user->ID;

	// If "Dismiss" link has been clicked, user meta field is added
	if ( isset( $_GET['dismiss_me'] ) && 'yes' == $_GET['dismiss_me'] ) {
		add_user_meta( $userid, 'bex_ignore_warning_notice', 'yes', true );
		return;
	}

	global $pagenow;

    if ($pagenow != 'plugins.php' && $pagenow != 'admin.php') {
        return;
    }

    // Only show this notice if user hasn't already dismissed it...
    if ( get_user_meta( $userid, 'bex_ignore_warning_notice' ) ) {
    	return;
    }

	$slug = 'svg-vector-icon-plugin';
	$path = $slug .'/wp-svg-icons.php';
	$plugins = get_plugins();

	// This plugin previously used the WP SVG Icons plugin. With version 1.3.0, it is no longer required.
	// Check to see if it exists and prompt to uninstall. The prompt can be dismissed.

	if (empty($plugins[$path])) {
		return;
	}

	$dismiss_url = $_SERVER['REQUEST_URI'];
	$dismiss_url .= ((strpos($dismiss_url,'?') > 0) ? '&' : '?');
	$dismiss_url .= 'dismiss_me=yes';

	echo '<div class="update-nag"><p>The <b>' . BEX_PLUGIN_NAME .'</b> plugin used to require the <b>WP SVG Icons</b> plugin. If you\'re not using <b>WP SVG Icons</b> elsewhere, it can be safely removed. <a href="' .$dismiss_url .'">Dismiss</a><br /><br />';

	if (is_plugin_active($path)) {
		$deactivate_url = wp_nonce_url(admin_url('plugins.php?action=deactivate&plugin=' .$path), 'deactivate-plugin_' .$path );
		echo '<a href="' .$deactivate_url .'">Deactivate Plugin</a>';
	}

	echo '</p></div>';
}

function bex_init() {
	register_setting( 'bex_option-display', 'bex_home_root');
	register_setting( 'bex_option-display', 'bex_folder');
	register_setting( 'bex_option-display', 'bex_format_moddate');
	register_setting( 'bex_option-display', 'bex_show_moddate');
	register_setting( 'bex_option-display', 'bex_show_size');
	register_setting( 'bex_option-display', 'bex_show_ext');
	register_setting( 'bex_option-display', 'bex_new_tab');
	register_setting( 'bex_option-display', 'bex_suppress_css');
	register_setting( 'bex_option-display', 'bex_download');
	register_setting( 'bex_option-display', 'bex_sort_dir');
	register_setting( 'bex_option-display', 'bex_natural_sort');
	register_setting( 'bex_option-display', 'bex_sort_folders');
	register_setting( 'bex_option-upload', 'bex_noauth_uploads');
	register_setting( 'bex_option-upload', 'bex_email_upload');
	register_setting( 'bex_option-upload', 'bex_allow_uploads');
	register_setting( 'bex_option-upload', 'bex_filenames_allow');
	register_setting( 'bex_option-upload', 'bex_filenames_exclude');
	register_setting( 'bex_option-auth', 'bex_folder_auth', 'bex_folder_auth_validate');
	register_setting( 'bex_option-setup', 'bex_dropbox_code2' );
	register_setting( 'bex_option-dropbox', 'bex_dropbox_token2' );
}

function bex_setup_menu(){
    add_menu_page( 'Blighty Explorer Page', 'Blighty Explorer', 'manage_options', 'blighty-explorer-plugin', 'bex_admin_settings', plugin_dir_url( __FILE__ ) .'/images/blighty-explorer-logo-16x16.png' );	
}

add_filter( 'plugin_action_links_blighty-explorer/blighty-explorer.php', 'bex_add_action_links' );

function bex_add_action_links ( $links ) {
	$url = '<a href="' . admin_url( 'admin.php?page=blighty-explorer-plugin' ) . '">Settings</a>';
	$mylinks = array( $url );
	return array_merge( $mylinks, $links );
}

function bex_admin_settings(){
	global $dropbox;

    $authOK = false;
    
    if (isset($_GET['code']) && $_GET['code'] != '') {
        update_option('bex_dropbox_code2',$_GET['code']);
        $authOK = true;
    } 

    $rc = bex_handle_dropbox_auth($dropbox);

    $defaultTab = ($rc == 0 ? 'display_options' : 'cloud_options');
    
    $activeTab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $defaultTab;

?>
	<div class="wrap">
		<h2><?php echo BEX_PLUGIN_NAME; ?> v<?php echo BEX_PLUGIN_VERSION; ?></h2>
		<?php
		if (isset($_GET['auth_callback']) && $authOK == false) {
			echo '<div class="error"><p>Error authenticating Dropbox.</p></div>';
		} elseif (isset($_GET['auth_callback'])) {
			echo '<div class="updated"><p>Dropbox connection successful.</p></div>';
		} elseif (isset($_GET['bex_reset'])) {
			echo '<div class="updated"><p>Dropbox connection has been reset.</p></div>';
		}
		if (!$dropbox->IsConnectionOK()) {
			echo '<div class="error"><p>Unable to use cURL function. Check your hosting configuration.</p></div>';
		}
		?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=blighty-explorer-plugin&tab=display_options" class="nav-tab <?php echo $activeTab == 'display_options' ? 'nav-tab-active' : ''; ?>">Display Options</a>
            <a href="?page=blighty-explorer-plugin&tab=access_options" class="nav-tab <?php echo $activeTab == 'access_options' ? 'nav-tab-active' : ''; ?>">Access Control</a>
            <a href="?page=blighty-explorer-plugin&tab=upload_options" class="nav-tab <?php echo $activeTab == 'upload_options' ? 'nav-tab-active' : ''; ?>">Upload Options</a>
            <a href="?page=blighty-explorer-plugin&tab=cloud_options" class="nav-tab <?php echo $activeTab == 'cloud_options' ? 'nav-tab-active' : ''; ?>">Cloud Setup</a>
            <a href="?page=blighty-explorer-plugin&tab=help_options" class="nav-tab <?php echo $activeTab == 'help_options' ? 'nav-tab-active' : ''; ?>">Help / FAQ</a>
        </h2>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div class="inner-sidebar">
					<div id="side-sortables" class="meta-box-sortables ui-sortable" style="position:relative;">
						<div class="postbox">
							<h3>Dropbox Information</h3>
							<div class="inside">
                                <?php
                                if ($dropbox->IsAuthorized()) {
                                    $info1 = $dropbox->GetAccountInfo();
                                    $info2 = $dropbox->GetSpaceUsage();
                                    $quota = $info2->allocation->allocated;
                                    $used = $info2->used;
                                    $pc = $used / $quota * 100;
                                    echo '<font style="background-color: green; color: white;">&nbsp;Connected&nbsp;</font><br /><br />';                                    
                                    echo '<b>Account:</b> ' .$info1->name->display_name .'<br />';
                                    echo '<b>Quota:</b> ' .bex_format_bytes($quota) .'<br />';
                                    echo '<b>Used:</b> ' .bex_format_bytes($used) .' (' .sprintf("%.1f%%", $pc) .')<br /><br />';
                                } elseif ($dropbox->IsConnectionOK()) {
                                    echo '<font style="background-color: goldenrod; color: white;">&nbsp;Not Connected&nbsp;</font><br /><br />';
                                } else {
                                    echo '<font style="background-color: firebrick; color: white;">&nbsp;cURL function not enabled&nbsp;</font><br /><br />';
                                }
                                ?>
							</div>
						</div>
						<div class="postbox">
							<h3>Help me help you!</h3>
							<div class="inside">
                                Hi, I'm Chris - the developer of Blighty Explorer. Did this plugin help you fill a need? Did it save you some development time? Please consider making a donation today. Thank you.<br /><br />
								<div align="center">
									<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
									<input type="hidden" name="cmd" value="_donations">
									<input type="hidden" name="business" value="2D9PDAS9FDDCA">
									<input type="hidden" name="lc" value="US">
									<input type="hidden" name="item_name" value="Blighty Explorer Plugin">
									<input type="hidden" name="item_number" value="BEP001A">
									<input type="hidden" name="button_subtype" value="services">
									<input type="hidden" name="no_note" value="1">
									<input type="hidden" name="no_shipping" value="1">
									<input type="hidden" name="currency_code" value="USD">
									<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donateCC_LG.gif:NonHosted">
									<input type="hidden" name="on0" value="website">
									<input type="hidden" name="os0" value="<?php echo $_SERVER['SERVER_NAME']; ?>">
									<input type="radio" name="amount" value="5">$5&nbsp;
									<input type="radio" name="amount" value="7">$7&nbsp;
									<input type="radio" name="amount" value="10">$10&nbsp;
									<input type="radio" name="amount" value="20">$20&nbsp;
									<input type="radio" name="amount" value="">Other<br /><br />
									<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
									</form>
								</div>
							</div>
						</div>
						<div class="postbox">
							<h3>Subscribe to receive updates</h3>
							<div class="inside">		
							    There is no obligation to subscribe in order to use this plugin, but if you'd like to receive updates about this and other Blighty plugins, you can sign up here.<br />
                                <!-- Begin MailChimp Signup Form -->
                                <link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
                                <style type="text/css">
                                    #mc_embed_signup{background:#fff; clear:left; }
                                </style>
                                <div id="mc_embed_signup">
                                    <form action="//blighty.us13.list-manage.com/subscribe/post?u=8b355a21422958b6cdac086a0&amp;id=f2dd2f7494" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                                        <div id="mc_embed_signup_scroll">
                                        <div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
                                        <div class="mc-field-group">
                                            <label for="mce-EMAIL">Email Address  <span class="asterisk">*</span></label>
                                            <input type="email" value="<?php echo get_option( 'admin_email' ); ?>" name="EMAIL" class="required email" id="mce-EMAIL">
                                        </div>
                                        <div class="mc-field-group">
                                            <label for="mce-NAME">Name </label>
                                            <input type="text" value="" name="NAME" class="" id="mce-NAME">
                                        </div>
                                        <div class="mc-field-group">
                                            <label for="mce-WEBSITE">This website </label>
                                            <b><?php echo $_SERVER['SERVER_NAME']; ?></b>
                                            <input type="hidden" value="<?php echo $_SERVER['SERVER_NAME']; ?>" name="WEBSITE" id="mce-WEBSITE">
                                        </div>
                                        <div class="mc-field-group">
                                            <label for="mce-PLUGIN">This plugin </label>
                                            <b>Blighty Explorer</b>
                                            <input type="hidden" value="1" name="group[2777][1]" id="mce-group[2777]-2777-0">
                                        </div>
                                        
                                        <div id="mce-responses" class="clear">
                                            <div class="response" id="mce-error-response" style="display:none"></div>
                                            <div class="response" id="mce-success-response" style="display:none"></div>
                                        </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                                        <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_8b355a21422958b6cdac086a0_f2dd2f7494" tabindex="-1" value=""></div>
                                        <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                                    </div>
                                    </form>
                                </div>
                                <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='NAME';ftypes[1]='text';fnames[2]='WEBSITE';ftypes[2]='text';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
                                <!--End mc_embed_signup-->
    							The information above is collected and processed using MailChimp. We do not share or sell data with other third-parties, and you can unsubscribe at any time.
							</div>
						</div>						
						<div class="postbox">
							<h3>Technical Support</h3>
							<div class="inside">
								If you need technical support or would like to see a new featured implemented, please provide your feedback via the <a href="https://wordpress.org/support/plugin/blighty-explorer">WordPress Plugin Forums</a>.
							</div>
						</div>
					</div>
				</div>

				<div id="post-body-content" class="has-sidebar-content">
					<div class="meta-box-sortables">
					    <?php if( $activeTab == 'help_options' ) { ?>
						<div class="xpostbox">
							<h3>Configuration and Usage</h3>
							<div class="inside">
								<ol>
									<li>Connect this plugin to your Dropbox account (see Cloud Setup above).</li>
									<li>This will create a subfolder called <b>Blighty Explorer</b> in your <b>Apps</b> folder within Dropbox (if it doesn't already exist).</li>
									<li>Place your folders and files you wish to share with this WordPress installation inside the <b>Apps/Blighty Explorer</b> subfolder.</li>
									<li>Use the shortcode <b>[bex_folder]</b> in your post or page to display a folder structure / file navigator.</li>
									<li>Use the shortcode <b>[bex_upload]</b> in your post or page to display a file upload dialog.</li>
								</ol>
								<br />
								<a href="https://wordpress.org/plugins/blighty-explorer/" target="_blank">Check out the WordPress site for FAQ and more.</a><br /><br />
							</div>
						</div>
					    <?php } elseif( $activeTab == 'cloud_options' ) { ?>
						<div class="xpostbox">
							<h3>Cloud Setup</h3>
							<div class="inside">
							    Dropbox Connection Status:&nbsp;
                                <?php                                
                                if ($dropbox->IsAuthorized()) { 
                                    echo '<a href="?page=blighty-explorer-plugin&bex_reset=1">Reset Dropbox connection</a><br /><br />';                                    
                                } elseif ($dropbox->IsConnectionOK()) {
                                    $auth_url = bex_setup_dropbox_auth($dropbox);
                                ?>
        						    <br />
    							    This plugin needs to be connected to your Dropbox account.<br /><br />
    							    This is a two-step process:
    							    <ol>
    							        <li><a href="<?php echo $auth_url; ?>">Authenticate with Dropbox</a></li>
    							        <li>Make sure you click on 'Allow Access' on Dropbox's website</li>
    							    </ol>
    							    (If it doesn't already exist, a subfolder called <b>Blighty Explorer</b> will be created in your <b>Apps</b> folder within Dropbox.)<br /><br />
                                <?php 
                                } else {
                                    echo 'Not connected.<br /><br />';
                                } ?>
							</div>
						</div>
					    <?php } elseif( $activeTab == 'display_options' ) { ?>						
						<div class="xpostbox">
							<h3>Display Options</h3>
							<div class="inside">
        						<?php if ($dropbox->IsAuthorized()) { ?>
								<form method="post" action="options.php">
								<?php

    						    settings_fields('bex_option-display');

								if ( get_option('bex_show_moddate') == '1' ) {
									$checkedModDate = 'checked ';
								} else {
									$checkedModDate = '';
								}

								if ( get_option('bex_show_size') == '1' ) {
									$checkedSize = ' checked';
								} else {
									$checkedSize = '';
								}

								if ( get_option('bex_show_ext','1') == '1') {
									$checkedExt = ' checked';
								} else {
									$checkedExt = '';
								}

								if ( get_option('bex_new_tab') == '1' ) {
									$checkedNewTab = ' checked';
								} else {
									$checkedNewTab = '';
								}
								
								$checkedSuppressCSS = array('','','');
								
								switch (get_option('bex_suppress_css')) {
								    case '1':
                                        $checkedSuppressCSS[1] = ' checked';
                                        break;
								    case '2':
                                        $checkedSuppressCSS[2] = ' checked';
                                        break;
                                    default:
                                        $checkedSuppressCSS[0] = ' checked';
                                }

								if ( get_option('bex_download') == '1' ) {
									$checkedDownload = ' checked';
								} else {
									$checkedDownload = '';
								}

								if ( get_option('bex_nav_type') == '1' ) {
									$navType0Checked = '';
									$navType1Checked = ' checked';
								} else {
									$navType0Checked = ' checked';
									$navType1Checked = '';
								}
								
								if ( get_option('bex_home_root') == 'F' ) {
									$checkedHomeRootH = '';
									$checkedHomeRootF = ' checked';
								} else {
									$checkedHomeRootH = ' checked';
									$checkedHomeRootF = '';
								}

								if ( get_option('bex_sort_dir') == 'D' ) {
									$checkedSortDirA = '';
									$checkedSortDirD = ' checked';
								} else {
									$checkedSortDirA = ' checked';
									$checkedSortDirD = '';
								}

                                if ( get_option('bex_natural_sort') == '1' ) {
                                    $checkedNaturalSort = ' checked';
                                } else {
                                    $checkedNaturalSort = '';
                                }
                                
                                if ( get_option('bex_sort_folders') == 'N' ) {
									$checkedSortFoldersN = ' checked';
									$checkedSortFoldersY = '';
                                } else {
									$checkedSortFoldersN = '';
									$checkedSortFoldersY = ' checked';
                                }

								echo 'By default, folders and files are shared from your <strong>Dropbox Folder/Apps/Blighty Explorer</strong>. ';
								echo 'If you want to share a subfolder under <strong>Apps/Blighty Explorer</strong>, set it here as the root folder. ';
								echo 'This allows you to share different subfolders on different WordPress installations.<br /><br />';
								echo '<b>Root folder:</b><br />';

								$files = $dropbox->GetFiles('');
								
								if (count($files) == 0) {
									echo '<input type="radio" name="bex_folder" value="" checked />/<br />';
								} else {
                                    if (get_option('bex_folder') == '' || get_option('bex_folder') == '/') {
                                        $checkedFolder = ' checked';
                                    } else {
                                        $checkedFolder = '';
                                    }
                                    echo '<input type="radio" name="bex_folder" value="" ' .$checkedFolder .' />/<br />';
									foreach ($files as $file) {
										if ($file->{'.tag'} == 'folder' && $file->name != BEX_UPLOADS_FOLDER) {

											if ($file->path_lower == strtolower(get_option('bex_folder'))) {
												$checkedFolder = ' checked';
											} else {
												$checkedFolder = '';
											}
											echo '<input type="radio" name="bex_folder" value="' .$file->path_lower .'"'.$checkedFolder .' />' .$file->name .'<br />';
										}
									}
								}

                                $now = date('m/d/Y h:i:s a', time());

                                $moddateFormats = array(
                                    "j M Y H:i",
                                    "d/m/Y H:i",
                                    "m/d/Y H:i",
                                    "Y-m-d H:i",
                                    "j M Y",
                                    "d/m/Y",
                                    "m/d/Y",
                                    "Y-m-d",
                                    );
                                    
                                $moddateSelected = get_option('bex_format_moddate', 'j M Y H:i');

								echo '<br />';
								echo '<b>Use "';
								_e('Home', 'blighty-explorer');
								echo '" as the root label or the root\'s folder name:</b> <input type="radio" name="bex_home_root" value="H"' .$checkedHomeRootH .' />';
								_e('Home', 'blighty-explorer');
								echo '&nbsp;';
								echo '<input type="radio" name="bex_home_root" value="F"' .$checkedHomeRootF .' />Folder name</b><br /><br />';
								echo '<b>Show modification date:</b>&nbsp;<input type="checkbox" name="bex_show_moddate" value="1"' .$checkedModDate .' />&nbsp;';
								echo '<b>Show size:</b>&nbsp;<input type="checkbox" name="bex_show_size" value="1"' .$checkedSize .' />&nbsp;';
								echo '<b>Show file extensions:</b>&nbsp;<input type="checkbox" name="bex_show_ext" value="1"' .$checkedExt .' /><br /><br />';
								echo '<b>Modification date format (if shown): ';
								echo '<select name="bex_format_moddate">';
								$iCount = count($moddateFormats);
								for ($i = 0; $i < $iCount ; $i++) {
								    if ($moddateFormats[$i] == $moddateSelected) {
								        $selected = 'selected ';
								    } else {
								        $selected = '';
								    }
    								echo '<option ' .$selected .'value="' .$moddateFormats[$i] .'">' .date($moddateFormats[$i], strtotime($now)) .'</option>';
    							}
								echo '</select>';
								echo '</b><br /><br />';
								echo '<b>Default filename sort:</b>&nbsp;<input type="radio" name="bex_sort_dir" value="A"' .$checkedSortDirA .' />Ascending&nbsp;';
								echo '<input type="radio" name="bex_sort_dir" value="D"' .$checkedSortDirD .' />Descending&nbsp;-&nbsp;';
                                echo '<b>Use natural sort order:</b>&nbsp;<input type="checkbox" name="bex_natural_sort" value="1"' .$checkedNaturalSort .' /><br />';
                                echo '<b>Sort folders to top of list:</b>&nbsp;<input type="radio" name="bex_sort_folders" value="Y"' .$checkedSortFoldersY .' />Yes&nbsp;';
								echo '<input type="radio" name="bex_sort_folders" value="N"' .$checkedSortFoldersN .' />No<br /><br />';
								echo '<b>Download Files:</b>&nbsp;<input type="checkbox" name="bex_download" value="1"' .$checkedDownload .' /> Files can either be shown in the browser (default) or selected to download.<br />';
								echo '<b>Open Files in new Tab/Window:</b>&nbsp;<input type="checkbox" name="bex_new_tab" value="1"' .$checkedNewTab .' /> Force a new tab/window to opened when a file is selected.<br />';
								echo '<br />';
								echo '<b>Select stylesheet:</b>&nbsp;<input type="radio" name="bex_suppress_css" value="0"' .$checkedSuppressCSS[0].' />Default&nbsp;&nbsp;';
								echo '<input type="radio" name="bex_suppress_css" value="2"' .$checkedSuppressCSS[2] .' />Minimal CSS&nbsp;&nbsp;';
								echo '<input type="radio" name="bex_suppress_css" value="1"' .$checkedSuppressCSS[1] .' />Suppress built-in CSS<br />';
								
								submit_button();
								
							    } ?>
                            </div>
                        </div>
    
                        <?php } elseif( $activeTab == 'upload_options' ) { ?>
                        <div class="xpostbox">
                            <h3>Upload Options</h3>
                            <div class="inside">
                                <?php if ($dropbox->IsAuthorized()) { 
                                    echo 'Max upload (as defined in php.ini): ' .bex_format_bytes(bex_max_upload_file_size());
                                ?><br /><br />
                                <form method="post" action="options.php">
                                <?php

                                settings_fields('bex_option-upload');

								if ( get_option('bex_email_upload') == '1' ) {
									$checkedEmail = ' checked';
								} else {
									$checkedEmail = '';
								}

								if ( get_option('bex_allow_uploads') == '1' ) {
									$checkedAllowUploads = ' checked';
								} else {
									$checkedAllowUploads = '';
								}

								$filenamesAllow = get_option('bex_filenames_allow');
								$filenamesExclude = get_option('bex_filenames_exclude');
								
								if ( get_option('bex_noauth_uploads') == '1' ) {
									$checkedNoAuthUploads = ' checked';
								} else {
									$checkedNoAuthUploads = '';
								}

								echo 'File uploads via this plugin will be stored in the folder <strong>' .BEX_UPLOADS_FOLDER .'</strong> under the <strong>Root folder</strong>. This will not be displayed via the plugin.<br /><br />';
								echo 'If you want to allow uploads into the folder that the user has navigated to, then check the <strong>Allow Uploads in Active Folder</strong> option below.<br /><br />';
								echo '<b>Allow uploads in Active Folder:</b>&nbsp;<input type="checkbox" name="bex_allow_uploads" value="1"' .$checkedAllowUploads .' />&nbsp;(Default is the _bex_uploads folder.)<br /><br />';
								echo '<b>Allow uploads to Dropbox when the WordPress user is not logged in:</b>&nbsp;<input type="checkbox" name="bex_noauth_uploads" value="1"' .$checkedNoAuthUploads .' /><br /><br />';
								echo '<b>Email admin on upload:</b>&nbsp;<input type="checkbox" name="bex_email_upload" value="1"' .$checkedEmail .' />';
								echo '&nbsp;Check this box to receive an email every time a user uploads a file.<br /><br />';
								echo '<b>Filename extensions to allow:</b>&nbsp;<input type="text" name="bex_filenames_allow" value="' .$filenamesAllow .'" />&nbsp;(comma separated e.g. docx,xlsx,txt,jpg)<br />';
								echo '<b>Filename extensions to exclude:</b>&nbsp;<input type="text" name="bex_filenames_exclude" value="' .$filenamesExclude .'" />&nbsp;(comma separated e.g. docx,xlsx,txt,jpg)<br /><br />';
								echo '(Note: If both \'allow\' and \'exclude\' are provided, the \'allow\' options take priority.)<br /><br />';

								submit_button();

								?>
								</form>
								<?php } ?>
							</div>
						</div>
                        <?php } elseif( $activeTab == 'access_options' ) { ?>
						<div class="xpostbox">
							<h3>Access Control</h3>
							<div class="inside">
							    <?php if ($dropbox->IsAuthorized()) { ?>
								<form method="post" action="options.php">
								<?php
									global $wp_roles;
									$roles = $wp_roles->get_names();
									sort($roles);

                                    settings_fields('bex_option-auth');

									$i = 1;

									echo 'Use these options to allow only logged-in WordPress users with specific roles access to individual folders under the <strong>Root Folder</strong>.<br /><br />';
									echo 'To restrict access to the plugin completely, use a plugin such as <a href="https://wordpress.org/plugins/user-specific-content" target="_blank">User Specific Content</a> in conjunction with this one.<br /><br />';
									echo '<b>Available Roles:</b><br />';
									echo 'Select the role(s) you want to set on the top-level folders below<br />';
									echo '<input type="checkbox" name="role_0" value="' .BEX_ANONYMOUS .'" />' .BEX_ANONYMOUS .'<br />';
									foreach ($roles as $role) {
										echo '<input type="checkbox" name="role_' .$i .'" value="' .$role .'" />' .$role .'<br />';
										$i++;
									}
									echo '<br />';
									echo '<b>Set on the following top-level folders:</b><br />';

									$folderAuth = get_option('bex_folder_auth');

									if (!$folderAuth) {
									    $newFolderAuth = true;
										$folderAuth = array();
									} else {
									    $newFolderAuth = false;
    									$folderAuth = array_change_key_case($folderAuth, CASE_LOWER);
									}

								    $rootFolder = untrailingslashit(get_option('bex_folder'));
									$files = $dropbox->GetFiles($rootFolder!='/'?$rootFolder:'');
									$i = 0;

									foreach ($files as $file) {

										$filePath = $file->path_lower;

										if ($file->{'.tag'} == 'folder' && $file->name != BEX_UPLOADS_FOLDER) {
											echo '<input type="checkbox" name="bex_folder_auth_' .$i .'" value="' .$file->path_lower .'">&nbsp;<b>' .$file->name .'</b> - (';
											if (isset($folderAuth[$filePath])) {
												echo $folderAuth[$filePath];
											} elseif (isset($folderAuth[$filePath ."/"])) {
												echo $folderAuth[$filePath ."/"];
											} else {
											    if ($newFolderAuth) {
											        echo BEX_ANONYMOUS;
											    } else {
    												echo BEX_ADMIN;
    											}
											}
											$i++;
											echo ')<br />';
										}
									}
									
									echo '<br /><b>Reset:</b><br />';
									echo '<input type="checkbox" name="ac_reset" /> Check this box to completely reset access control settings.<br />';

									submit_button();
								?>
								</form>
								<?php } ?>
							</div>
						</div>
						<?php 
					}
				 ?>												
				</div>
				<?php echo BEX_PLUGIN_NAME; ?> v<?php echo BEX_PLUGIN_VERSION; ?> by <a href="http://blighty.net" target="_blank">Blighty</a>
			</div>

	</div>
<?php
}

function bex_folder_auth_validate($input) {

    if (isset($_POST['ac_reset'])) {
        delete_option('bex_folder_auth');
        return null;
    }

	$role = '';
	foreach ($_POST as $field => $value) {
		if (substr($field,0,5) == 'role_') {
				$role .= $value .', ';
		}
	}

	$role = substr($role,0,strlen($role)-2);

	$folderAuth = get_option('bex_folder_auth');
    $folderAuth = array_change_key_case($folderAuth, CASE_LOWER);

	foreach ($_POST as $field => $value) {
		if (substr($field,0,16) == 'bex_folder_auth_') {
		        unset ($folderAuth[$value ."/"]);
				$folderAuth[$value] = $role;
		}
	}

	return $folderAuth;
}

?>
