<?php
/*
Plugin Name: Youtube to WP Post
Description: This plugin is used for creating post of youtube data with thumbnail as post's featured image.
Author: Robin Gupta(robingupta0512@gmail.com)
Version: 1.0

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_menu', 'yp_plugin_menu');
function yp_plugin_menu() {
	   add_menu_page('Youtube to Post', 'Youtube to Post', 'manage_options', 'yttowp', 'yp_init');        
  }
function yp_init(){
    include('yp-admin.php');
} 
 function getVideoID($url)
   {
      $url = trim($url);
      // make sure url has http on it
      if(substr($url, 0, 4) != "http") {
         $url = "http://".$url;
      }
      
      // make sure it has the www on it
      if(substr($url, 7, 4) != "www.") {
        $url = str_replace('http://', 'http://www.', $url);
      }

      // extract the youtube ID from the url
      if(substr($url, 0, 31) == "http://www.youtube.com/watch?v=") {
         $id = substr($url, 31, 11);
      }
         
      return $id;      
   } 
   
   function checkYoutubeId($id) {
	if (!$data = @file_get_contents("http://gdata.youtube.com/feeds/api/videos/".$id)) return 0;
	if ($data == "Video not found") return 0;
	return 1;
}
   
  function getVideoDetails($id)
   {
      // create an array to return
      $videoDetails = Array();
      
      // get the xml data from youtube
      $url = "http://gdata.youtube.com/feeds/api/videos/".$id;
      $xml = simplexml_load_file($url);	
      return $xml;
   }  
   
   function fetch_image($url) {
		if ( function_exists("curl_init") ) {
			return curl_fetch_image($url);
		} elseif ( ini_get("allow_url_fopen") ) {
			return fopen_fetch_image($url);
		}
	}
	function curl_fetch_image($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$image = curl_exec($ch);
		curl_close($ch);
		return $image;
	}
	function fopen_fetch_image($url) {
		$image = file_get_contents($url, false, $context);
		return $image;
	}
?>