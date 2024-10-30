<?php
/**

Copyright (C) 2015-2017 Chris Murfin (Blighty)

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

v2.1.2

**/

function bex_upload( $atts ) {

	$atts = shortcode_atts(
		array(
			'root' => get_option('bex_folder'),
		), $atts, 'bex_folder' );

	$rootFolder = trailingslashit($atts['root']);

	if (!empty($_GET["folder"])) {
		$folder = esc_attr($_GET["folder"]);
		$folder = ltrim($folder, ".");
		$folder = ltrim($folder, "/");
	} else {
		$folder = "";
	}

	$out = '<form id="bexUpload" class="bex-upload-form" action="' .admin_url( 'admin-ajax.php' ) .'" method="post" enctype="multipart/form-data">';
	$out .= '  ' .__('Select file to upload:','blighty-explorer');
	$out .= '  <input type="file" name="bexFile" id="bexFile">';
	$out .= '  <input type="submit" value="' .__('Upload','blighty-explorer') .'" name="bexSubmit" id="bexSubmit" class="bex-button-submit">';
	$out .= '  <input type="hidden" name="bexFolder" id="bexFolder" value="' .$rootFolder .$folder .'">';
	$out .= '  <br /><br /><div class="bex-progress">';
	$out .= '  <div class="bex-bar"></div >';
	$out .= '  <div class="bex-percent">0%</div >';
	$out .= '</div><br />';
    $out .= '<div id="bexStatus">' .__('Ready to upload.','blighty-explorer') .'</div>';
	$out .= '</form>';

	return $out;
}

function bex_submission_processor_nopriv() {
	if (get_option('bex_noauth_uploads')) {
		bex_submission_processor();
	} else {
		_e ('No access.', 'blighty-explorer');
	}
	die();
}

function bex_submission_processor() {

	if (empty($_FILES["bexFile"])) {
		_e ('No file selected.', 'blighty-explorer');
		die();
	}

    $allow = get_option('bex_filenames_allow');
    if (!empty($allow)) {
        $match = str_replace(',','|',$allow);
        if (!preg_match('/^.*\.('.$match.')$/i', $_FILES["bexFile"]["name"])) {
	    	_e ('This type of file is not allowed.', 'blighty-explorer');
            die();
        }   
    } else {
        $exclude = get_option('bex_filenames_exclude');
        if (!empty($exclude)) {
            $match = str_replace(',','|',$exclude);
            if (preg_match('/^.*\.('.$match.')$/i', $_FILES["bexFile"]["name"])) {
    	    	_e ('This type of file is not allowed.', 'blighty-explorer');
                die();
            }   
        }    
    }

	global $dropbox;

    bex_handle_dropbox_auth($dropbox);
	
	$rootFolder = trailingslashit(get_option('bex_folder'));
	if (get_option('bex_allow_uploads')) {
		$folder = esc_attr($_POST["bexFolder"]);
	} else {
		$folder = $rootFolder .BEX_UPLOADS_FOLDER;
	}

	$workingFolder = trailingslashit($folder);

	if (!empty($_POST['bexSubmit'])) {
		$dropbox->UploadFile($_FILES["bexFile"]["tmp_name"], $workingFolder .$_FILES["bexFile"]["name"], false);
	}

	if (get_option('bex_email_upload')) {

    	if (is_user_logged_in()) {
			global $current_user;
    		get_currentuserinfo();
	    	$userLogin = $current_user->user_login;
    		$userEmail = $current_user->user_email;
    	} else {
    		$userLogin = 'anonymous';
	    	$userEmail = 'no email';
    	}

		$headers = 'From: ' .get_bloginfo('name') .' <' .get_bloginfo('admin_email') .'>' . "\r\n";
		$subj = '[' .get_bloginfo('name') .'] File Upload';
		$body = 'The file "' .$_FILES["bexFile"]["name"] .'" has just been uploaded to ' .$workingFolder
			  .' by ' .$userLogin .' (' .$userEmail .')';
		wp_mail( get_bloginfo('admin_email'), $subj, $body, $headers );
	}

	_e ('File uploaded. Ready for next file.', 'blighty-explorer');
	die();

}

?>
