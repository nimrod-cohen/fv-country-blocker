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

    echo '<div class="updated"><p>Settings saved.</p></div>';
  }

  // Get the plugin version
  $plugin_version = fv_country_blocker_get_plugin_data('Version');

  // Get the custom MMDB path
  $custom_mmdb_path = get_option('fv_country_blocker_custom_mmdb_path', '');

  ?>
    <div class="wrap">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
            <span class="fv-country-blocker-version">v<?php echo esc_html($plugin_version); ?></span>
        </h1>
        <form action="" method="post">
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
            </table>

            <h2>Select Countries to Block</h2>
            <input type="text" id="country-search" placeholder="Search countries..." style="margin-bottom: 10px; width: 100%; max-width: 400px;">
            <div class="fv-country-list" style="max-height: 400px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px;">
                <?php foreach ($countries as $code => $names): ?>
                    <label class="country-item" style="display: inline-block; width: 200px; margin-bottom: 10px;" data-code="<?php echo esc_attr(strtolower($code)); ?>" data-name="<?php echo esc_attr(strtolower($names["name"])); ?>" data-long-name="<?php echo esc_attr(strtolower($names["long_name"])); ?>">
                        <input type="checkbox" name="blocked_countries[]" value="<?php echo esc_attr($code); ?>"
                            <?php checked(in_array($code, $blocked_countries));?>>
                        <img src="<?php echo esc_url(fv_country_blocker_get_flag_url($code)); ?>" alt="<?php echo esc_attr($names["name"]); ?> flag" style="width: 16px; height: 11px; margin-right: 5px;">
                        <?php echo esc_html($names["name"]); ?>
                    </label>
                <?php endforeach;?>
            </div>

            <?php submit_button();?>
        </form>
        <h2>GeoIP Database Information</h2>
        <p>Last updated: <?php echo esc_html(get_option('fv_country_blocker_last_update', 'Never')); ?></p>
    </div>
    <?php
}

function fv_country_blocker_get_flag_url($country_code) {
  // You'll need to have flag images for this to work.
  // You can use a flag API or store flag images in your plugin.
  return FV_COUNTRY_BLOCKER_PLUGIN_URL . 'assets/flags/' . strtolower($country_code) . '.svg';
}
