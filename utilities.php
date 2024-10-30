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

v2.1.7

**/

function bex_max_upload_file_size() {
/**
* Converts shorthands like “2M” or “512K” to bytes
*
* @param $size
* @return mixed
*
* Thanks to Paul Melekhov and lostinspace for this code...
*
*/
    $normalize = function($size) {
        if (preg_match('/^([\d\.]+)([KMG])$/i', $size, $match)) {
            $pos = array_search($match[2], array("K", "M", "G"));
            if ($pos !== false) {
                $size = $match[1] * pow(1024, $pos + 1);
            }
        }
        return $size;
    };

    $max_upload = $normalize(ini_get('upload_max_filesize'));

    $max_post = (ini_get('post_max_size') == 0) ? function(){throw new Exception('Check Your php.ini settings');} : $normalize(ini_get('post_max_size'));

    $memory_limit = (ini_get('memory_limit') == -1) ? $max_post : $normalize(ini_get('memory_limit'));

    if ($memory_limit < $max_post || $memory_limit < $max_upload)
        return $memory_limit;

    if ($max_post < $max_upload)
        return $max_post;

    $maxFileSize = min($max_upload, $max_post, $memory_limit);
    
    return $maxFileSize;
}

function bex_format_bytes($bytes, $precision = 2) {
	// Thanks to PHP.Net for this piece of code...
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    // $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function bex_handle_dropbox_auth($dropbox)
{
	if(!empty($_GET['bex_reset'])) {  // are we performing a dropbox connection reset?
		delete_option('bex_dropbox_token2');
		delete_option('bex_dropbox_code2');
		return 2;
	} elseif ($dropbox->IsConnectionOK()) {
        $dbx_token = get_option('bex_dropbox_token2',null);
        if (!is_null($dbx_token) && $dbx_token != '') {
            $dropbox->SetAccessToken($dbx_token);
            return 0;
        } else {
            $dbx_code = get_option('bex_dropbox_code2',null);    
            if (!is_null($dbx_code) && $dbx_code != '') {
                update_option('bex_dropbox_token2',$dropbox->GetAccessToken($dbx_code,"https://auth.blighty.net/dropbox.php"));
                return 0;
            } else { // Attempt to migrate from Oauth1
                $dbx_token1 = get_option('bex_dropbox_token',null);
                if (!is_null($dbx_token1) && $dbx_token1 != '') {
                    $oauth1 = @unserialize($dbx_token1);
                    $dbx_token = $dropbox->MigrateToken($oauth1['t'],$oauth1['s']);
                    update_option('bex_dropbox_token2',$dbx_token);
                    $dropbox->SetAccessToken($dbx_token);
                    return 0;
                } else {
                    return 1;
                }
            }
        }
    } else {
        return 3;
    }
}

function bex_setup_dropbox_auth($dropbox) {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        $http = "https";
    } else {
        $http = "http";
    }

    $server = $_SERVER['HTTP_HOST'];
    if (empty($server)) {
        $server = $_SERVER['SERVER_NAME'] .':' .$_SERVER['SERVER_PORT'];
    }

    $return_url = $http ."://".$server.$_SERVER['SCRIPT_NAME']."?page=blighty-explorer-plugin&auth_callback=1";

    return $dropbox->BuildAuthorizeUrl($return_url, "https://auth.blighty.net/dropbox.php");
}

?>
