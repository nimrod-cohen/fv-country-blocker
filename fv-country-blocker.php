<?php
/**
 * Plugin Name: FV Country Blocker
 * Plugin URI: https://github.com/nimrod-cohen/fv-country-blocker
 * Description: Block visitors from specific countries using MaxMind GeoIP database.
 * Version: 1.2.1
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

define('FV_COUNTRY_BLOCKER_PLUGIN_DIR', __DIR__);
define('FV_COUNTRY_BLOCKER_PLUGIN_URL', plugin_dir_url(__FILE__));

class FV_Country_Blocker {
  private static $instance;

  public static function get_instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct() {
    $this->load_dependencies();
    $this->define_admin_hooks();
    $this->define_public_hooks();

    register_activation_hook(__FILE__, ['FV_Country_Blocker', 'activate']);
    register_deactivation_hook(__FILE__, ['FV_Country_Blocker', 'deactivate']);

    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
  }

  // Get plugin data
  public static function get_plugin_data($key = null) {
    static $plugin_data;
    if (!isset($plugin_data)) {
      if (!function_exists('get_plugin_data')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
      }
      $plugin_data = get_plugin_data(__FILE__);
    }
    return $key ? ($plugin_data[$key] ?? null) : $plugin_data;
  }

  // Enqueue admin styles
  public function enqueue_admin_styles() {
    $screen = get_current_screen();
    if ($screen->id !== 'settings_page_fv-country-blocker') {
      return;
    }

    $plugin_version = self::get_plugin_data('Version');
    $cachebust = "?time=" . date('Y_m_d_H') . "&v=" . $plugin_version;

    wp_enqueue_script('fv-country-blocker-admin-search', FV_COUNTRY_BLOCKER_PLUGIN_URL . 'assets/js/admin.js' . $cachebust, array(), $plugin_version, true);
    wp_enqueue_style('fv-country-blocker-admin', FV_COUNTRY_BLOCKER_PLUGIN_URL . 'assets/css/admin.css' . $cachebust);

    wp_localize_script('fv-country-blocker-admin-search', 'fvCountryBlocker', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('fv-country-blocker-nonce')
    ]);
  }

  //add settings link on plugin page
  function plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=fv-country-blocker') . '">Settings</a>';
    array_unshift($links, $settings_link); // Add the settings link at the beginning
    return $links;
  }

  public static function activate() {
    // Activation code here
    // For example, you might want to set default options
    if (!get_option('fv_country_blocker_blocked_countries')) {
      update_option('fv_country_blocker_blocked_countries', '');
    }

    // Schedule the cron job for database updates
    if (!wp_next_scheduled('fv_country_blocker_update_db')) {
      wp_schedule_event(time(), 'weekly', 'fv_country_blocker_update_db');
    }
  }

  public static function deactivate() {
    // Deactivation code here
    // For example, you might want to clear scheduled events
    wp_clear_scheduled_hook('fv_country_blocker_update_db');

    // You might also want to clean up any temporary files or data
    // Be careful not to delete user settings unless specifically required
  }

  private function load_dependencies() {
    require_once __DIR__ . '/admin/admin-page.php';
  }

  private function define_admin_hooks() {
    add_action('admin_menu', [$this, 'add_admin_menu']);
    add_action('admin_init', [$this, 'register_settings']);
    add_action('fv_country_blocker_update_db', [$this, 'update_geoip_database']);

    //checking for plugin updates
    add_action('admin_init', function () {
      $updater = new GitHubPluginUpdater(__FILE__);
    });
  }

  private function define_public_hooks() {
    add_action('init', [$this, 'check_visitor_country']);
    add_action('wp_ajax_fv_country_blocker_test_ip', [$this, 'test_ip']);
  }

  public function test_ip() {
    $ip = trim($_POST['ip']);

    //validate nonce
    if (!wp_verify_nonce($_POST['nonce'], 'fv-country-blocker-nonce')) {
      wp_send_json_error('Invalid nonce');
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
      wp_send_json_error('Invalid IP address');
    }

    $country = FV_GeoIP::get_visitor_country($ip);

    if (!$country) {
      wp_send_json_error('Could not determine country');
    }

    wp_send_json_success($country);
  }

  public function add_admin_menu() {
    add_options_page(
      'FV Country Blocker Settings',
      'FV Country Blocker',
      'manage_options',
      'fv-country-blocker',
      'fv_country_blocker_admin_page'
    );
  }

  public function register_settings() {
    register_setting('fv_country_blocker_options', 'fv_country_blocker_license_key');
    register_setting('fv_country_blocker_options', 'fv_country_blocker_blocked_countries', [
      'type' => 'array',
      'sanitize_callback' => [$this, 'sanitize_blocked_countries']
    ]);
  }

  public function sanitize_blocked_countries($input) {
    $valid_countries = array_keys(FV_GeoIP::get_countries_list());
    return array_intersect($input, $valid_countries);
  }

  public function update_geoip_database() {
    $updater = new FV_Country_Blocker_Updater();
    $updater->update_database();
  }

  public function check_visitor_country() {
    $force = $_GET["force_country_ip"] ?? false;

    // Get the user's IP address
    $ip = FV_GeoIP::get_user_ip();

    if (!$force && (
      current_user_can('administrator')
      || wp_get_environment_type() != 'production'
      || $ip == '127.0.0.1')) {
      return;
    }

    if ($force && $ip == '127.0.0.1') {
      $ip = $force;
    }

    $visitor_country = FV_GeoIP::get_visitor_country($ip);
    if (!$visitor_country) {
      error_log('FV Country Blocker: Could not determine visitor country.');
      return;
    }

    $blocked = get_option('fv_country_blocker_blocked_countries', []);

    if (in_array($visitor_country, $blocked)) {
      // Redirect or display a message to the visitor
      $custom_blocking_html = get_option('fv_country_blocker_custom_blocking_html', '');
      status_header(403);
      die($custom_blocking_html);
    }

    //all good.
  }

  public function run() {
    if (!wp_next_scheduled('fv_country_blocker_update_db')) {
      wp_schedule_event(time(), 'weekly', 'fv_country_blocker_update_db');
    }
  }
}

// Include the main plugin classes
$directory = __DIR__ . '/includes';
$files = glob($directory . '/*.php');
foreach ($files as $file) {
  require_once $file;
}

// Run the plugin
$plugin = FV_Country_Blocker::get_instance();
$plugin->run();