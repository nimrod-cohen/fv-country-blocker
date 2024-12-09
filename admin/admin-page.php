<?php

function fv_country_blocker_admin_page() {
  // Get the list of countries
  $countries = FV_GeoIP::get_countries_list();

  // Get currently blocked countries
  $blocked_countries = get_option('fv_country_blocker_blocked_countries', []);

  // Ensure $blocked_countries is an array
  if (!is_array($blocked_countries)) {
    $blocked_countries = explode(',', $blocked_countries);
  }

  if (isset($_POST['submit'])) {
    // Update blocked countries
    $blocked_countries = isset($_POST['blocked_countries']) ? $_POST['blocked_countries'] : [];
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

    // Update custom user IP header
    $custom_user_ip_header = sanitize_text_field($_POST['fv_country_blocker_custom_user_ip_header']);
    update_option('fv_country_blocker_custom_user_ip_header', $custom_user_ip_header);

    echo '<div class="updated"><p>Settings saved.</p></div>';
  }

  // Get the plugin version
  $plugin_version = FV_Country_Blocker::get_plugin_data('Version');

  // Get the custom MMDB path
  $custom_mmdb_path = get_option('fv_country_blocker_custom_mmdb_path', '');

  $actual_mmdb_path = FV_Country_Blocker_Updater::get_mmdb_path();
  //check last update date and time
  if (file_exists($actual_mmdb_path)) {
    $last_update = date('Y-m-d H:i:s', filemtime($actual_mmdb_path));
  } else {
    $last_update = "<span class='error'>MMDB file cannot be found</span>";

    // Check if the license key is set and the custom path is empty - so we can do something about it
    if (!empty($license_key) && empty($custom_mmdb_path)) {
      $updater = new FV_Country_Blocker_Updater();
      $updater->update_database();
    }

    if (file_exists($custom_mmdb_path)) {
      $last_update = date('Y-m-d H:i:s', filemtime($custom_mmdb_path));
    }
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
            <a href="#test-ip" class="nav-tab">Test IP</a>
        </h2>

        <form action="" method="post">
            <?php require_once 'settings.php';?>
            <?php require_once 'countries.php';?>
            <?php require_once 'test-ip.php'?>
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
