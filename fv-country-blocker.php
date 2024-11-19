<?php
/**
 * Plugin Name: FV Country Blocker
 * Plugin URI: https://github.com/nimrod-cohen/fv-country-blocker
 * Description: Block visitors from specific countries using MaxMind GeoIP database.
 * Version: 1.1.2
 * Author: nimrod-cohen
 * Author URI: https://github.com/nimrod-cohen/fv-country-blocker
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: fv-country-blocker
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

define('FV_COUNTRY_BLOCKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FV_COUNTRY_BLOCKER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main plugin classes
$directory = FV_COUNTRY_BLOCKER_PLUGIN_DIR . '/includes';
$files = glob($directory . '/*.php');
foreach ($files as $file) {
  require_once $file;
}

register_activation_hook(__FILE__, ['FV_Country_Blocker', 'activate']);
register_deactivation_hook(__FILE__, ['FV_Country_Blocker', 'deactivate']);

// Run the plugin
function run_fv_country_blocker() {
  $plugin = new FV_Country_Blocker();
  $plugin->run();
}
run_fv_country_blocker();

// Enqueue admin styles
function fv_country_blocker_enqueue_admin_styles() {
  $screen = get_current_screen();
  if ($screen->id !== 'settings_page_fv-country-blocker') {
    return;
  }

  $plugin_version = fv_country_blocker_get_plugin_data('Version');
  $cachebust = "?time=" . date('Y_m_d_H') . "&v=" . $plugin_version;

  wp_enqueue_script('fv-country-blocker-admin-search', FV_COUNTRY_BLOCKER_PLUGIN_URL . 'assets/js/admin.js' . $cachebust, array(), $plugin_version, true);
  wp_enqueue_style('fv-country-blocker-admin', FV_COUNTRY_BLOCKER_PLUGIN_URL . 'assets/css/admin.css' . $cachebust);

  wp_localize_script('fv-country-blocker-admin-search', 'fvCountryBlocker', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('fv-country-blocker-nonce')
  ]);
}
add_action('admin_enqueue_scripts', 'fv_country_blocker_enqueue_admin_styles');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'fv_country_blocker_plugin_action_links');

//add settings link on plugin page
function fv_country_blocker_plugin_action_links($links) {
  $settings_link = '<a href="' . admin_url('options-general.php?page=fv-country-blocker') . '">Settings</a>';
  array_unshift($links, $settings_link); // Add the settings link at the beginning
  return $links;
}

// Get plugin data
function fv_country_blocker_get_plugin_data($key = null) {
  static $plugin_data;
  if (!isset($plugin_data)) {
    if (!function_exists('get_plugin_data')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $plugin_data = get_plugin_data(__FILE__);
  }
  return $key ? ($plugin_data[$key] ?? null) : $plugin_data;
}