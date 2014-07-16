<?php

if( ! class_exists( 'AllSpark' ) )
	require_once( 'AllSpark/AllSpark.class.php' );


class IGHashTagPlugin extends AllSpark {
	public $authkey;
	public $hashtag;
	public $clientID;
	public $clientSecret;
	public $client_auth;
	public $redirect_url_path = 'instagram_auth';
	
	private $clientIDKey = 'hbt_IGHashTagPlugin_clientID';
	private $clientSecretKey = 'hbt_IGHashTagPlugin_clientSecret';
	private $clientAuthKey = 'hbt_IGHashTagPlugin_clientKey';
	private $clientDetailsKey = 'hbt_IGHashTagPlugin_clientDetails';
	private $hashTagKey = 'hbt_IGHashTagPlugin_hashTag';
	
	public function __construct(){
		parent::__construct();
		$this->listen_for_ajax_action('instagram_auth');
		
		$this->clientID = get_option($this->clientIDKey);
		$this->clientSecret = get_option($this->clientSecretKey);
		$this->client_auth = get_option($this->clientAuthKey);
		
		$this->authkey  = get_option($this->clientAuthKey);
		$this->hashtag = get_option($this->hashTagKey);
	}
	
	public function init(){
		if(isset($_GET['start_auth'])){
			$clientID = $_POST['clientID'];
			$clientSecret = $_POST['clientSecret'];
			
			update_option($this->clientIDKey, $clientID);
			update_option($this->clientSecretKey, $clientSecret);
			
			$redirect_url = urlencode($this->get_redirect_url());
			
			wp_redirect( "https://api.instagram.com/oauth/authorize/?client_id=$clientID&redirect_uri=$redirect_url&response_type=code");
			exit;
		}
		
		if(isset($_GET['doing_auth'])){
			
			$redirect_url = $this->get_redirect_url();
			
			$authcode = get_transient('ig-auth');
			$authcode = $authcode['code'];
			delete_transient('ig-auth-error');
			
			if($this->client_auth == ''){
				
				$response = wp_remote_post( 'https://instagram.com/oauth/access_token', array(
					'body' => array(
						'client_id' => $this->clientID,
						'client_secret' => $this->clientSecret,
						'grant_type' => 'authorization_code',
						'redirect_uri' => $this->get_redirect_url(),
						'code' => $authcode
					)
				));
				
				$authorization = json_decode(wp_remote_retrieve_body($response));
				
				update_option($this->clientAuthKey, $authorization->access_token);
				update_option($this->clientDetailsKey, $authorization->user);
				
				wp_redirect( admin_url( 'options-general.php?page=instagram-feed' ) );
			}
		}
		
		if(isset($_GET['set_hashtag'])){
			update_option($this->hashTagKey, $_POST['hashtag']);
			wp_redirect( admin_url( 'options-general.php?page=instagram-feed' ) );
		}
		
		if(isset($_GET['disconnect_auth'])){
			$this->reset();	
			wp_redirect( admin_url( 'options-general.php?page=instagram-feed' ) );
		}
		
		delete_transient( 'ig-auth' );
	}
	
	public function pluginDidActivate(){
		parent::pluginDidActivate();
		
		add_option( $this->clientIDKey, '', '', 'yes' );
		add_option( $this->clientSecretKey, '', '', 'yes' );
		add_option( $this->clientAuthKey, '', '', 'yes' );
	}
	
	public function pluginDidDeactivate(){
		parent::pluginDidDeactivate();
		$this->reset();	
	}
	
	private function reset(){
		delete_option( $this->clientIDKey );
		delete_option( $this->clientSecretKey );
		delete_option( $this->clientAuthKey );
		delete_option( $this->clientDetailsKey );
		delete_option( $this->hashTagKey );
	}
	
	public function get_redirect_url(){
		return admin_url('/admin-ajax.php?action=' . $this->redirect_url_path);
	}
	
	public function admin_menu(){
		add_options_page( 'Instagram Feed', 'Instagram Feed', 'moderate_comments', 'instagram-feed', array(&$this, 'do_admin_ui'));
	}
	
	public function fetch_feed(){
		if(!$this->hashtag || !$this->authkey){
			return array();
		}
		else{
			return $this->fetch_hashtag_photos($this->hashtag);
		}
	}
	
	public function fetch_hashtag_photos($hashtag){
		if(!$this->authkey)
			return false;

		return $this->fetch_photos_from_url("https://api.instagram.com/v1/tags/" . $hashtag . "/media/recent");
	}
	
	public function fetch_user_photos_with_handle($handle){
		if(!$this->authkey)
			return false;
		
		return $this->fetch_user_photos($this->fetch_id_for_handle($handle));
	}
	
	public function fetch_user_photos($user_id){
		if(!$this->authkey)
			return false;
		
		return $this->fetch_photos_from_url("https://api.instagram.com/v1/users/" . $user_id . "/media/recent");
	}
	
	private function fetch_id_for_handle($handle){
		
		if(get_transient( __CLASS__ . __FUNCTION__ . $handle)){
			return get_transient( __CLASS__ . __FUNCTION__ . $handle);
		}
		
		$raw_response = wp_remote_get("https://api.instagram.com/v1/users/search?q=" . $handle . "&access_token=" . $this->authkey);
		$users = json_decode(wp_remote_retrieve_body($raw_response))->data;
		
		foreach($users as $user){
			if($user->username == $handle){
				set_transient( __CLASS__ . __FUNCTION__ . $handle, $user->id, 0 );
			}
		}
		
		return $this->fetch_id_for_handle($handle);
	}
	
	private function fetch_photos_from_url($url){
		$raw_response = wp_remote_get($url . "?access_token=" . $this->authkey);
		$response = wp_remote_retrieve_body($raw_response);
		$response_obj = json_decode($response);
		
		return $response_obj->data;
	}
	
	public function do_admin_ui(){
		require_once('ui/main.ui.php');
	}
	
	protected function get_client_details(){
		return get_option($this->clientDetailsKey);
	}
	
	public function instagram_auth(){
		if(isset($_REQUEST['error_reason'])){
			set_transient( 'ig-auth-error', $_REQUEST, YEAR_IN_SECONDS );
			wp_redirect( admin_url( 'options-general.php?page=instagram-feed' ) );
			exit;
		}
		
		if(isset($_REQUEST['code'])){
			set_transient( 'ig-auth', $_REQUEST, YEAR_IN_SECONDS );
			wp_redirect( admin_url( 'options-general.php?page=instagram-feed&doing_auth=true' ) );
			exit;
		}
		
		wp_redirect( admin_url( 'options-general.php?page=instagram-feed' ) );
	}
	
}

IGHashTagPlugin::getInstance();
