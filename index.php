<?php
/*
Plugin Name: Instagram Feeds
Plugin URI: http://ignitionmedia.ca
Description: Makes it easy to embed instagram feeds in sites
Version: 1.0
Author: Ignition Media
Author URI: http://ignitiomedia.ca
*/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

require_once 'IGHashTagPlugin.class.php';

$ig_plugin = new IGHashTagPlugin();

function get_instagram_feed(){
	global $ig_plugin;
	return $ig_plugin->fetch_feed();
}

function get_instagram_feed_with_hashtag($hashtag = 'hotsandwiches'){
	global $ig_plugin;
	return $ig_plugin->fetch_hashtag_photos($hashtag);
}

function get_instagram_feed_with_handle($handle){
	global $ig_plugin;
	return $ig_plugin->fetch_user_photos_with_handle($handle);
}

function get_instagram_feed_with_user_id($id){
	global $ig_plugin;
	return $ig_plugin->fetch_user_photos($id);
}
