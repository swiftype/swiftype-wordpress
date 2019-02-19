<div class="wrap">
	<h2 class="swiftype-header">Swiftype Search Plugin</h2><br/>

	<form name="swiftype_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
		<?php wp_nonce_field('swiftype-nonce'); ?>
		<input type="hidden" name="action" value="swiftype_create_engine">
		<table class="widefat" style="width: 650px;">
			<thead>
				<tr>
					<th class="row-title">Configure your Search Engine</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<br/>
						Please name the search engine the plugin will use:<br/>
						<ul>
							<li>
								<label>Engine name:</label>
								<input type="text" name="engine_name" class="regular-text" placeholder="engine name" />
								<span class="description">e.g. Wordpress Site Search</span>
							</li>
							<br/>
							<input type="submit" name="Submit" value="Create Engine"  class="button-primary" id="create_engine"/>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<br/>

	<hr/>
	<p>
		If you're having trouble with the Swiftype plugin, or would like to reconfigure your search engine,<br/>
		you may clear your Swiftype Configuration by clicking the button below. This will allow you to enter<br/>
		a new API key and create a new search engine.
	</p>
	<form name="swiftype_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
		<?php wp_nonce_field('swiftype-nonce'); ?>
		<input type="hidden" name="action" value="swiftype_clear_config">
		<input type="submit" name="Submit" value="Clear Swiftype Configuration"  class="button-primary" />
	</form>

</div>
