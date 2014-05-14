<?php
$feed = $this->fetch_feed();
$auth_error = get_transient( 'ig-auth-error' );
delete_transient( 'ig-auth-error' );
?>

<?php if($auth_error !== false): ?>
<div class="error">
	<p>
		<strong>Unable to Authorize App</strong><br />
		<?php echo $auth_error['error_description']; ?>
	</p>
</div>
<?php endif; ?>

<div class="wrap">
	<h2>Instagram Feed</h2>
	
	<?php if(!$this->client_auth): ?>
		
	<p>To get started, you must authorize this website with your instagram account</p>
	
	<form method="post" action="<?php echo admin_url('options-general.php?page=instagram-feed&start_auth'); ?>">
	
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="clientID">Client ID</label></th>
					<td>
						<input name="clientID" type="text" id="clientID" value="<?php echo get_option($this->clientIDKey); ?>" class="regular-text">
						<p class="description">This value comes from the Instagram dashboard.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="clientSecret">Client Secret</label></th>
					<td>
						<input name="clientSecret" type="text" id="clientSecret" value="<?php echo get_option($this->clientSecretKey); ?>" class="regular-text">
						<p class="description">This value comes from the Instagram dashboard.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="redirect_uri">Redirect URL</label></th>
					<td>
						<input name="redirect_uri" type="text" id="redirect_uri" value="<?php echo $this->get_redirect_url(); ?>" disabled="disabled" class="regular-text">
						<p class="description">You'll need to enter this value on the instagram dashboard.</p>
					</td>
				</tr>
			</tbody>
		</table>
	
		<input type="submit" class="button button-primary" value="Authorize App"/>
	
	</form>
	<?php else: ?>
		<?php $client_details = get_option($this->clientDetailsKey); ?>
			
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="middle"><label>Connected As</label>
					</th>
					<td style="width: 250px;">
						<img src="<?php echo $client_details->profile_picture; ?>" width="75" height="75" />
						<br /><strong><?php echo $client_details->username; ?></strong>
					</td>
					<td>
						<form method="post" action="<?php echo admin_url('options-general.php?page=instagram-feed&disconnect_auth'); ?>">
							<input type="submit" class="button button-secondary" value="Disconnect" />
						</form>
					</td>
				</tr>
			</tbody>
		</table>	
		
		<form method="post" action="<?php echo admin_url('options-general.php?page=instagram-feed&set_hashtag'); ?>">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="hashtag">Hashtag</label></th>
					<td>
						<input name="hashtag" type="text" id="hashtag" value="<?php echo get_option($this->hashTagKey); ?>" class="regular-text">
						<p class="description">This is the hashtag you want to monitor.</p>
					</td>
				</tr>
			</tbody>
		</table>
	
		<input type="submit" class="button button-primary" value="Update Hashtag" />
		</form>

	<?php endif; ?>
	
	

	<?php if(count($feed) > 0): ?>
	<div style="margin-top: 45px;">
		<h3>Your Photo Feed: </h3>
		<div>
			<?php foreach($feed as $photo):?>
			<img src="<?php echo $photo->thumbnail->url; ?>" width="<?php echo $photo->thumbnail->width; ?>" height="<?php echo $photo->thumbnail->height; ?>" />
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	
</div>
