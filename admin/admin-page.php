<?php

function fv_country_blocker_admin_page() {
  // Get the list of countries
  $countries = FV_GeoIP::get_countries_list();

  // Get currently blocked countries
  $blocked_countries = get_option('fv_country_blocker_blocked_countries', array());

  // Ensure $blocked_countries is an array
  if (!is_array($blocked_countries)) {
    $blocked_countries = explode(',', $blocked_countries);
  }

  if (isset($_POST['submit'])) {
    // Update blocked countries
    $blocked_countries = isset($_POST['blocked_countries']) ? $_POST['blocked_countries'] : array();
    update_option('fv_country_blocker_blocked_countries', $blocked_countries);

    // Update license key
    $license_key = sanitize_text_field($_POST['fv_country_blocker_license_key']);
    update_option('fv_country_blocker_license_key', $license_key);

    // Update custom MMDB path
    $custom_mmdb_path = sanitize_text_field($_POST['fv_country_blocker_custom_mmdb_path']);
    update_option('fv_country_blocker_custom_mmdb_path', $custom_mmdb_path);

    // Update custom blocking HTML
    $custom_blocking_html = wp_kses_post($_POST['fv_country_blocker_custom_blocking_html']);
    update_option('fv_country_blocker_custom_blocking_html', $custom_blocking_html);

    echo '<div class="updated"><p>Settings saved.</p></div>';
  }

  // Get the plugin version
  $plugin_version = fv_country_blocker_get_plugin_data('Version');

  // Get the custom MMDB path
  $custom_mmdb_path = get_option('fv_country_blocker_custom_mmdb_path', '');

  $actual_mmdb_path = FV_Country_Blocker_Updater::get_mmdb_path();
  //check last update date and time
  if (file_exists($actual_mmdb_path)) {
    $last_update = date('Y-m-d H:i:s', filemtime($actual_mmdb_path));
  } else {
    $last_update = "<span class='error'>MMDB file cannot be found</span>";
  }

  // Get the custom blocking HTML
  $custom_blocking_html = get_option('fv_country_blocker_custom_blocking_html', '');
  if (empty($custom_blocking_html)) {
    $custom_blocking_html = '<h1>Access Denied</h1><p>Sorry, access from your country is not allowed.</p>';
  }

  ?>
    <div class="wrap">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
            <span class="fv-country-blocker-version">v<?php echo esc_html($plugin_version); ?></span>
        </h1>

        <h2 class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab nav-tab-active">Settings</a>
            <a href="#countries" class="nav-tab">Blocked Countries</a>
        </h2>

        <form action="" method="post">
            <div id="settings" class="tab-content">
                <h2>MaxMind MMDB Settings</h2>
                <table class="form-table">
                  <tr valign="top">
                      <th scope="row">MaxMind License Key</th>
                      <td><input type="text" name="fv_country_blocker_license_key" class="maxmind-input" value="<?php echo esc_attr(get_option('fv_country_blocker_license_key')); ?>" /></td>
                  </tr>
                  <tr valign="top">
                      <th scope="row">Custom MMDB File Path</th>
                      <td>
                          <input type="text" name="fv_country_blocker_custom_mmdb_path" value="<?php echo esc_attr($custom_mmdb_path); ?>" class="maxmind-input" />
                          <p class="description">Leave empty to use the current WP installation path. if you have multiple WP installs, and want to use the same MMDB for all of them, you can use the custom path, it is then your responsibility to make sure that the file is downloaded regularly.</p>
                      </td>
                  </tr>
                  <tr valign="top">
                        <th scope="row">Custom Blocking HTML</th>
                        <td>
                            <textarea name="fv_country_blocker_custom_blocking_html" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_blocking_html); ?></textarea>
                            <p class="description">Enter the HTML to be displayed when a visit is blocked. You can use the following placeholders: {COUNTRY_CODE}, {COUNTRY_NAME}, {IP_ADDRESS}</p>
                        </td>
                  </tr>
                  <tr valign="top">
                    <th scope="row">Last Update</th>
                    <td><?php echo $last_update; ?></td>
                  </tr>
                </table>
            </div>

            <div id="countries" class="tab-content" style="display:none;">
                <h2>Select countries to block</h2>
                <input type="text" id="country-search" placeholder="Search countries..." style="margin-bottom: 10px; width: 100%; max-width: 400px;">
                <div class="fv-country-list" style="max-height: 400px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px;">
                    <?php foreach ($countries as $code => $names): ?>
                        <label class="country-item"
                          data-code="<?php echo esc_attr(strtolower($code)); ?>"
                          data-name="<?php echo esc_attr(strtolower($names["name"])); ?>"
                          title="<?php echo esc_attr(strtolower($names["long_name"])); ?>"
                          data-long-name="<?php echo esc_attr(strtolower($names["long_name"])); ?>">
                          <input type="checkbox" name="blocked_countries[]" value="<?php echo esc_attr($code); ?>"
                          <?php checked(in_array($code, $blocked_countries));?>>
                          <img src="<?php echo esc_url(fv_country_blocker_get_flag_url($code)); ?>"
                            alt="<?php echo esc_attr($names["name"]); ?> flag"
                            class="flag">
                          <?php echo esc_html($names["name"]); ?>
                        </label>
                    <?php endforeach;?>
                </div>
            </div>

            <?php submit_button();?>
        </form>
    </div>
    <?php
}

function fv_country_blocker_get_flag_url($country_code) {
  // You'll need to have flag images for this to work.
  // You can use a flag API or store flag images in your plugin.
  return FV_COUNTRY_BLOCKER_PLUGIN_URL . 'assets/flags/' . strtolower($country_code) . '.svg';
}
