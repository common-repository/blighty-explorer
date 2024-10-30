<?php
/**

Copyright (C) 2015-2024 Chris Murfin (Blighty)

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

v2.3.0

**/

function bex_folder( $atts ) {
	global $dropbox;
	$cache = array();

	// Allow attribute override for root folder...

	$atts = shortcode_atts(
		array(
			'root' => get_option('bex_folder'),
			'sortdir' => get_option('bex_sort_dir'),
			'filepick' => 0,
		), $atts, 'bex_folder' );
	
	$expURI = explode("?", $_SERVER['REQUEST_URI']);
    $virtualScriptName=reset($expURI);

	if ($atts['filepick'] == '0') {
    	$rootFolder = untrailingslashit($atts['root']);
    	set_transient( 'bex_root_' .$virtualScriptName, $rootFolder );
    } else {
        $rootFolder = untrailingslashit( get_transient( 'bex_root_' .$virtualScriptName ));
    }
        
	if (!empty($_GET['sortdir'])) {
		$sortDir = $_GET['sortdir'];
	} else {
		$sortDir = $atts['sortdir'];
	}

	if (!empty($_GET['sortby'])) {
		$sortBy = $_GET['sortby'];
	} else {
		$sortBy = '';
	}

	if (substr($rootFolder,0,1) != '/') {
		$rootFolder = '/' .$rootFolder;
	}
	
	if (get_option('bex_home_root') == 'F') {
	    $rootLabel = $rootFolder; //substr($rootFolder,0,-1);
	    $slash = strrpos($rootLabel,'/') + 1;
	    $rootLabel = substr($rootLabel,$slash);
	} else {
	    $rootLabel = __('Home','blighty-explorer');
	}

	$user = wp_get_current_user();

	$userRoles = (array) $user->roles;
	
	$mapIcons = array(
		".mp3" => "music",
		".mp3" => "music",
		".m4v" => "film",
		".gif" => "page_white_picture",
		".jpg" => "page_white_picture",
		".png" => "page_white_picture",
		".pdf" => "page_white_acrobat"
	);

	if (!empty($_GET["folder"])) {
		$folder = $_GET["folder"];
		$folder = ltrim($folder, ".");
		$folder = str_replace("\'", "'", $folder); 
	} else {
		$folder = "";
	}

	$workingFolder = untrailingslashit($rootFolder .$folder);
	$folder = untrailingslashit($folder);
	
	if (!bex_can_access($workingFolder, $rootFolder, $userRoles)) {
		return __('You do not have access to this folder.','blighty-explorer');
	}
	
	$file = !empty($_GET["file"]) ? $_GET["file"] : null;
	$rc = bex_handle_dropbox_auth($dropbox);

	if (!is_null($file)) {
	    $dl = (get_option('bex_download') == '1') ? '?dl=1' : '?dl=0&raw=1';
	    
		$url = str_replace('?dl=0','',$dropbox->GetLink(urldecode($rootFolder .$file)));
    	wp_redirect( $url . $dl, 302 );
		exit;
	} 

	$cache = get_transient( 'bex_cache' );
	if ($cache === false) $cache = [];

	$splits1 = explode('/',$rootFolder);
	$splits2 = explode('/',$workingFolder);
	$size1 = count($splits1);
	$size2 = count($splits2);


	// use the cache, otherwise cache result from Dropbox...
	if (isset($cache[$workingFolder])) {
		$files = $cache[$workingFolder];
	} else {
		$files = $dropbox->GetFiles($workingFolder!='/' ? $workingFolder : '');
		$cache[$workingFolder] = $files;
		set_transient( 'bex_cache', $cache, 60 );
	}

	$out = '<div class="bex-wrapper">';

	global $wp;
	if (!empty($wp->query_string)) {
		$thisQS = '?' .$wp->query_string .'&';
	} else {
		$thisQS = '?';
	}

	if ( $sortDir == 'D' ) {
		$newSortDir = 'A';
	} else {
		$newSortDir = 'D';
	}

	$pluginPath = plugin_dir_path( __FILE__ );

	if (substr($folder, 0, strlen($rootFolder)) == $rootFolder) {
		$folder = substr($folder, strlen($rootFolder));
	}

	// Default navigation: Display a cookie trail above folders/files...
	$out .= '<div class="bex-cookietrail">';
	$out .= '<img class="bex-img" src="' .plugins_url( 'icons/folder.png', __FILE__ ) .'" /> ';
	$out .= '<a href="' .$thisQS .'folder=/&sortdir=' .$sortDir .'">' .$rootLabel .'</a><br />';
	if (strlen($folder) > 1) {
		$splits = explode('/',untrailingslashit($folder));
		$size = count($splits);

		$j = 1;
		for ($i = 1; $i < $size; $i++) {
			$slashpos = strpos($folder,"/",$j);
			$j = $slashpos + 1;
			$out .= str_repeat("&nbsp;",$i * 2 + 2) ." &raquo; ";
			if ($slashpos > 0) {
    			$qsfolder = substr($folder,0,$slashpos);
			} else {
    			$qsfolder = $folder;
			}
			$out .= '<a href="' .$thisQS .'folder=' .$qsfolder .'&sortdir=' .$sortDir .'">' .$splits[$i] .'</a><br />';
			
		}
	}
	$out .= '</div>';
	$out .= '<div class="bex-table">';
	$out .= '<div class="bex-header">';
	$out .= '<div class="bex-cell"><a href="' .$thisQS .'folder=' .urlencode($folder) .'&sortdir=' .$newSortDir .'&sortby=name">';
	$out .= __('Name','blighty-explorer');
	$out .= '</a></div>';
	if (get_option('bex_show_moddate')) {
		$out .= '<div class="bex-cell-r"><a href="' .$thisQS .'folder=' .urlencode($folder) .'&sortdir=' .$newSortDir .'&sortby=date">';
		$out .= __('Date','blighty-explorer');
		$out .= '</a></div>';
	}
	if (get_option('bex_show_size')) {
		$out .= '<div class="bex-cell-r">';
		$out .= __('Size','blighty-explorer');
		$out .= '</div>';
	}
	$out .= '</div>';
	// Sort the folder/file structure...
	if ($sortBy == 'date') {
		usort($files, function ($a, $b) use($sortDir) {
			return bex_sort_compare_date($a, $b, $sortDir);
		});
    } else {
		uasort($files, function ($a, $b) use($sortDir) {
			return bex_sort_compare($a, $b, $sortDir);
		});
    }
    
    if (get_option('bex_new_tab')) {
        $target = ' target="_blank"';
    } else {
        $target = '';    
    }
    
	$i = 1;
	foreach ($files as $file) {
	
		$filePath = $file->path_display;
		$filePathWorking = $file->name;

		$len = strlen($rootFolder);
		if (strcasecmp(substr($filePath,0,$len),$rootFolder) == 0) {
			$filePath = urlencode(substr($filePath,$len));
		}
		$len = strlen($workingFolder);
		if (strcasecmp(substr($filePathWorking,0,$len),$workingFolder) == 0) {
			$filePathWorking = substr($filePathWorking,$len);
		}

		if ($file->{'.tag'} == 'folder' && (
				$filePathWorking == BEX_UPLOADS_FOLDER
				|| bex_can_access($file->path_lower,$rootFolder,$userRoles) == false)) {
		// Do nothing, i.e. suppress displaying the BEX_UPLOADS_FOLDER...
		// Or prevent access to folder if not allowed for role...
		} else {
			$i = 1 - $i;

			$out .= '<div class="bex-row-' .$i .'">';

            $dot = strrpos($filePathWorking,".");

            if ($dot > -1) {
                $ext = strtolower(substr($filePathWorking,$dot));
                if (get_option('bex_show_ext','1') != '1') {
                    $filePathWorking = substr($filePathWorking,0,$dot);
                }
            } else {
                $ext = 'ZZZZZ';
            }
            
            if (!empty($mapIcons[$ext])) {
                $icon = $mapIcons[$ext];
            } else {
                $icon = "page_white";        
            }
            
			if ($file->{'.tag'} == 'folder') {
				$out .= '<div class="bex-cell"><img class="bex-img" src="' .plugins_url( 'icons/folder.png', __FILE__ ) .'" />&nbsp;';
				$out .= '<a href="' .$thisQS .'folder=' .$filePath .'&sortdir=' .$sortDir .'">' .$filePathWorking ."</a></div>";
				if (get_option('bex_show_moddate')) {
					$out .= '<div class="bex-cell-r">&nbsp;</div>';
				}
				if (get_option('bex_show_size')) {
					$out .= '<div class="bex-cell-r">&nbsp;</div>';
				}
			} else {
				$out .= '<div class="bex-cell"><img class="bex-img" src="' .plugins_url( 'icons/'. $icon .'.png', __FILE__ ) .'" />&nbsp;';
				$out .= '<a href="' .$thisQS .'file=' .urlencode($filePath) .'"' .$target .'>' .$filePathWorking ."</a></div>";
				if (get_option('bex_show_moddate')) {
					$out .= '<div class="bex-cell-r">' .date(get_option('bex_format_moddate', 'j M Y H:i'),strtotime($file->client_modified)) . '</div>';
				}
				if (get_option('bex_show_size')) {
					$out .= '<div class="bex-cell-r">' .bex_format_bytes($file->size) .'</div>';
				}
			}
			$out .= '</div>';
		}
	}
	$out .= '</div>';
	$out .= '</div>';

	return $out;
}

function bex_sort_compare($a, $b, $sortDir) {
	if ((($a->{'.tag'} == 'folder') == ($b->{'.tag'} == 'folder') && (get_option('bex_sort_folders', 'Y') == 'Y'))
      || (get_option('bex_sort_folders') != 'Y')) {
		if ( $sortDir == 'D' ) {
    		if (get_option('bex_natural_sort') == '1') {
				return strnatcasecmp($b->name, $a->name);
			} else {
				return strcasecmp($b->name, $a->name);
			}
		} else {
			if (get_option('bex_natural_sort') == '1') {
				return strnatcasecmp($a->name, $b->name);
			} else {
				return strcasecmp($a->name, $b->name);
			}
		}
    } else if ($a->{'.tag'} == 'folder') {
    	return -1;
    } else {
    	return 1;
    }
}

function bex_sort_compare_date($a, $b, $sortDir) {
	if (($a->{'.tag'} == 'folder') == ($b->{'.tag'} == 'folder') && ($a->{'.tag'} != 'folder')) {
        if ( $sortDir == 'D' ) {
            return strtotime($b->client_modified)>strtotime($a->client_modified);
        } else {
            return strtotime($a->client_modified)>strtotime($b->client_modified);
        }
    } else if ($a->{'.tag'} == 'folder') {
    	return -1;
    } else {
    	return 1;
    }
}

function bex_can_access($efn,$rootFolder,$userRoles) {

	if ($rootFolder == $efn) {
		return true;
	}

	// Get the allowable roles to access folders...
	$folderAuth = get_option('bex_folder_auth');
	if (!$folderAuth) {
		// no special access rights so allow access..
		return true;
	}

	$canAccess = false;
	$efnFound = false;
	foreach($folderAuth as $folder=>$auth) {
	    $folder = untrailingslashit($folder); 
    
		// find a match on this level or higher level...
		if (strcasecmp(substr($efn .'/',0,strlen($folder)+1),$folder.'/') == 0) {
			$efnFound = true;
			if ($auth == BEX_ANONYMOUS) {
				return true;
			} else {
				$roles = explode(',',$auth);
				foreach ($roles as $role) {
					if ( in_array( strtolower(trim($role)), $userRoles ) ) {
						return true;
					}
				}
			}
		}
	}

	if (!$efnFound) {
        if ( in_array( strtolower(BEX_ADMIN), $userRoles ) ) {
            return true;
        } else {
            return false;
        }
	} else {
		return false;
    }

}


?>