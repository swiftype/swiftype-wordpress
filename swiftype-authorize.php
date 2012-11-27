<div class="wrap">
	<h2 class="swiftype-header">Swiftype Search Plugin</h2><br/>
	<form name="swiftype_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
		<?php wp_nonce_field('swiftype-nonce'); ?>
		<input type="hidden" name="action" value="swiftype_set_api_key">
		<table class="widefat" style="width: 650px;">
			<thead>
				<tr>
					<th class="row-title">Authorize the Swiftype Search Plugin</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						Thanks for installing the Swiftype Search Plugin for Wordpress. Please enter your Swiftype API key in the field below and click 'Authorize' to get started.
						If you don't have an API Key, you can get one by signing up for a free account at <a href="http://swiftype.com/users/sign_up" target="_new">swiftype.com</a>.
						You will find your API Key at the top of the Swiftype <b>Account Settings</b> screen.<br/><br/>
						<ul>
							<li>
								<label>Swiftype API Key:</label>
								<input type="text" name="api_key" class="regular-text" />
								<input type="submit" name="Submit" value="Authorize" class="button-primary" />
							</li>
					</td>
				</tr>
			</tbody>
		</table>
	</form>

</div>