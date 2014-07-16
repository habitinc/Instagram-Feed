<?php
/*
Plugin Name: Instagram Feeds
Plugin URI: https://github.com/habitinc/events
Description: Makes it easy to embed instagram feeds in sites
Version: 1.1
Author: Habit
Author URI: http://habithq.ca
*/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

require_once 'IGHashTagPlugin.class.php';

$ig_plugin = IGHashTagPlugin::getInstance();

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
