<?php

  $api_key = get_option('swiftype_api_key');
  $client = new SwiftypeClient;
  $client->set_api_key($api_key);
  $engines = $client->get_engines();

?>

<div class="wrap">
  <h2 class="swiftype-header">Swiftype Search Plugin</h2><br/>

  <form name="swiftype_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="action" value="set_engine">

<?php
  if(count($engines) > 0) {
?>

    <table class="widefat" style="width: 650px;">
      <thead>
        <tr>
          <th class="row-title">Configure your Search Engine</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            It looks like you have already created an engine in your Swiftype account. If you would like to use one of those existing engines as the
            search engine for your blog, select it from the drop-down below. Otherwise, you should create a new engine by entering a name in the text field below.<br/><br/>
            <ul>
              <li>
                <label>Use an existing engine:</label>
                <select name="engine_slug">
                  <option value=''>choose an engine</option>
                  <?php
                    foreach($engines as $engine) {
                      print("<option value='" . $engine['slug'] . "'>" . $engine['name'] . "</option>");
                    }
                  ?>
                </select>
              </li>
              <li>
                <b>OR</b>
              </li>
              <li>
                <label>Create a new engine:</label>
                <input type="text" name="engine_name" class="regular-text" />
                <span class="description">e.g. Wordpress Site Search</span>
              </li>
          </td>
        </tr>
      </tbody>
    </table>

<?php
  } else {
?>

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
      </tbody>
    </table>

<?php
  }
?>
    <br/>
    <input type="submit" name="Submit" value="Save Engine Configuration"  class="button-primary" />
  </form>
</div>

