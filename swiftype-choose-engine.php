<?php
  $api_key = get_option( 'swiftype_api_key' );
  $client = new SwiftypeClient;
  $client->set_api_key( $api_key );
  $engines = $client->get_engines();
?>

<div class="wrap">
  <h2 class="swiftype-header">Swiftype Search Plugin</h2><br/>

  <form name="swiftype_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="action" value="swiftype_set_engine">

    <table class="widefat" style="width: 650px;">
      <thead>
        <tr>
          <th class="row-title">Configure your Search Engine</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            Please create a search engine for the plugin to use.<br/>
            <ul>
              <li>
                <label>Engine name:</label>
                <input type="text" name="engine_name" class="regular-text" />
                <span class="description">e.g. Wordpress Site Search</span>
              </li>
          </td>
        </tr>
        <tr>
          <td>
            Use an existing engine.<br/>
            <ul>
              <li>
                <label>Engine key:</label>
                <input type="text" name="engine_key" class="regular-text" />
              </li>
          </td>
        </tr>
      </tbody>
    </table>

    <br/>
    <input type="submit" name="Submit" value="Save Engine Configuration"  class="button-primary" />
  </form>

  <hr/>
  <p>
    If you're having trouble with the Swiftype plugin, or would like to reconfigure your search engine,<br/>
    you may clear your Swiftype Configuration by clicking the button below. This will allow you to enter<br/>
    a new API key and create a new search engine.
  </p>
  <form name="swiftype_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="action" value="swiftype_clear_config">
    <input type="submit" name="Submit" value="Clear Swiftype Configuration"  class="button-primary" />
  </form>

</div>