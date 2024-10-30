<?php
/** 
 * DropboxClient2 - Interface to Dropbox API v2
 *
 * Author: Chris Murfin (Blighty)
 * Author URI: http://blighty.net
 * License: GPLv3 or later
 * Version: 1.1.0
 *
 **/
 
 /**

Copyright (C) 2015-2017 Chris Murfin

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
 
class DropboxClient2 {

	const API_URL = "https://api.dropboxapi.com/";
	const API_OAUTH_URL = "https://www.dropbox.com/oauth2/";
	const API_CONTENT = "https://content.dropboxapi.com/";
	const API_VER = "2/";

	const MAX_UPLOAD_CHUNK_SIZE = 150000000; // 150MBish

	private $accessToken;
	private $code;
	private $appKey;
	private $appSecret;
	private $locale;
	private $rootPath;
	private $useCurl;
	
	function __construct ($app_params, $locale = "en")
	{
		$this->appParams = $app_params;
		if(empty($app_params['app_key']))
			throw new DropboxException("App Key is empty!");
		
		$this->appKey = $app_params['app_key'];
		$this->appSecret = $app_params['app_secret'];
		$this->locale = $locale;
		$this->rootPath = empty($app_params['app_full_access']) ? "sandbox" : "dropbox";
		$this->accessToken = null;
		$this->code = null;				
		$this->useCurl = function_exists('curl_init');
	}

	public function GetAccessToken($code = null, $redirectUri)
	{
		if (!empty($this->accessToken)) 
		    return $this->accessToken;
		
		if (empty($code)) 
		    $code = $this->code;	
		
		if (empty($code)) 
		    throw new DropboxException('Request code required!');	
		
		$response = $this->CallAPI("oauth2/token","code=" .$code ."&grant_type=authorization_code&client_id=" .$this->appKey ."&client_secret=" .$this->appSecret ."&redirect_uri=" .$redirectUri);
		if (empty($response))
			throw new DropboxException(sprintf('Could not get access token! (request code: %s)', $code));
    		
	    return ($this->accessToken = $response->access_token);
	}

	public function SetAccessToken($token)
	{
		if(empty($token)) 
		    throw new DropboxException('Passed invalid access token.');
		
		$this->accessToken = $token;
	}	

	public function IsConnectionOK()
	{
		return ($this->useCurl);
	}

	public function IsAuthorized()
	{
		if(empty($this->accessToken)) {
		    return false;	
		} else {
    		return true;
    	}
	}
	
	public function GetAccountInfo()
	{
		return $this->CallAPI(self::API_VER ."users/get_current_account");
	}

	public function GetSpaceUsage()
	{
		return $this->CallAPI(self::API_VER ."users/get_space_usage");
	}

	public function GetFiles($dropbox_path='', $recursive=false, $include_deleted=false)
	{
		if(is_object($dropbox_path) && !empty($dropbox_path->path)) 
		    $dropbox_path = $dropbox_path->path;
		    
		return $this->getFileTree($dropbox_path, $include_deleted, $recursive ? 1000 : 0);
	}

	public function getFileTree($path="", $include_deleted = false, $max_depth = 0, $depth=0)
	{
		static $files;
		if($depth == 0) 
		    $files = array();
		
		$params = array("path"=>$path);
		$dir = $this->CallAPI(self::API_VER .'/files/list_folder', null, $params);
		
		if(empty($dir) || !is_object($dir)) 
		    return false;
		
		if(!empty($dir->error)) 
		    throw new DropboxException($dir->error);
		
		foreach($dir->entries as $item)
		{
			$files[$item->name] = $item;
			if($item->{".tag"} == 'folder' && $depth < $max_depth)
			{
				$this->getFileTree($item->name, $include_deleted, $max_depth, $depth+1);
			}
		}
		
		return $files;
	}

	public function GetLink($file) 
	{
	    // try to get existing link...
		$params = array("path"=>"$file");
		$response = $this->CallAPI(self::API_VER .'/sharing/list_shared_links', null, $params);
		
		if (isset($response->links[0]->url)) {
    	    return $response->links[0]->url;
        }
        
        // if no link found, then create a new one...
		$params = array("path"=>"$file");
		$response = $this->CallAPI(self::API_VER .'/sharing/create_shared_link_with_settings', null, $params);
	    return $response->url;
	}

	public function UploadFile($src_file, $dropbox_path='', $overwrite=true, $parent_rev=null)
	{
		$file_size = filesize($src_file);
        $fh = fopen($src_file,'rb');
        
        if($fh === false)
            throw new DropboxException();
        
        $uploadID = null;
        $offset = 0;

	    $ftt = true; // first time thru
        
        while(!feof($fh)) {		

            $content = fread($fh, self::MAX_UPLOAD_CHUNK_SIZE);
            $offset += strlen($content);

            if ($ftt) {
                $params = array('close' => false);
                $response = $this->CallAPI(self::API_VER .'/files/upload_session/start', null, $params, true, $content);	
                $ftt = false;
                if (isset($response->session_id)) {
                    $uploadID = $response->session_id;
                }                               
            } else {
                $params = array('cursor' => array('session_id' => $uploadID, 'offset' => $offset));
                $response = $this->CallAPI(self::API_VER .'files/upload_session/append_v2', null, $params, true, $content);                
            }

        }				

        $params = array('cursor' => array('session_id' => $uploadID, 'offset' => $offset), 'commit' => array('path' => $dropbox_path, 'mode' => 'add'));
        $response = $this->CallAPI(self::API_VER .'files/upload_session/finish', null, $params, true, "");

        unset($content);
	    
    }
    
    public function MigrateToken($token,$secret) {
    	$params = array("oauth1_token"=>"$token","oauth1_token_secret"=>"$secret");
		$response = $this->CallAPI(self::API_VER .'/auth/token/from_oauth1', null, $params, false, null, false);
		return $response->oauth2_token;
    }
    
    private function CallAPI($path,$qs = null,$params = array(),$content = false, $contentData = null, $bearer = true)
    {
        if (!$this->useCurl) {
            echo 'Unable to use cURL function.<br />';
            return null;
        }
        
        if (!$content) {
            $serviceURL = $this->cleanURL(self::API_URL .$path);
        } else {
            $serviceURL = $this->cleanURL(self::API_CONTENT .$path);
        }
        if (!is_null($qs)) $serviceURL .= "?" .$qs;
        $ch = curl_init($serviceURL);
        
        $postFields = json_encode($params);    
            
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if (is_null($qs)) {
            if (!empty($params)) {
                if (!$content) {
                    if ($bearer) {
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Authorization: Bearer ' .$this->accessToken,
                            'Content-Type: application/json'
                        ));
                    } else {
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Authorization: Basic ' .base64_encode($this->appKey .':' .$this->appSecret),
                            'Content-Type: application/json'
                        ));
                    }
                } else {
                    if (is_null($contentData)) {
                         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                             'Authorization: Bearer ' .$this->accessToken,
                             'Content-Type: ',
                             'Dropbox-API-Arg: ' .$postFields
                         ));
                    } else {                    
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Authorization: Bearer ' .$this->accessToken,
                            'Content-Type: application/octet-stream',
                            'Dropbox-API-Arg: ' .$postFields
                        ));
                    }
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $contentData);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                }
            } else {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' .$this->accessToken
                ));
            }
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 

        if (!$content && !empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }
                
        $response = curl_exec($ch);

        if ($response === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);
            die('error occurred during curl exec. Additional info: ' . var_export($info));
        }
    
        curl_close($ch);
    
        $decoded = json_decode($response);

        if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
            die('error occurred: ' . $decoded->response->errormessage);
        }

        return $decoded;
    }
    
	public function BuildAuthorizeUrl($returnUri = null, $redirectUri = null)
	{
		return self::API_OAUTH_URL ."authorize?response_type=code&client_id=" .$this->appKey ."&redirect_uri=" .$redirectUri ."&state=" .rawurlencode($returnUri);
	}    
    
	private function cleanUrl($url) {
		$p = substr($url,0,8);
		$url = str_replace('//','/', str_replace('\\','/',substr($url,8)));
		$url = rawurlencode($url);
		$url = str_replace('%2F', '/', $url);
		return $p.$url;
	}
    
}

class DropboxException extends Exception {
	
	public function __construct($err = null, $isDebug = FALSE) 
	{
		if(is_null($err)) {
			$el = error_get_last();
			$this->message = $el['message'];
			$this->file = $el['file'];
			$this->line = $el['line'];
		} else
			$this->message = $err;
		self::log_error($err);
		if ($isDebug)
		{
			self::display_error($err, TRUE);
		}
	}
	
	public static function log_error($err)
	{
		error_log($err, 0);		
	}
	
	public static function display_error($err, $kill = FALSE)
	{
		print_r($err);
		if ($kill === FALSE)
		{
			die();
		}
	}
}

?>
