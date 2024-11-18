<?php

class FV_Country_Blocker {

  protected $loader;
  protected $plugin_name;
  protected $version;

  public function __construct() {
    $this->plugin_name = 'fv-country-blocker';
    $this->version = fv_country_blocker_get_plugin_data('Version');
    $this->load_dependencies();
    $this->define_admin_hooks();
    $this->define_public_hooks();
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
    require_once FV_COUNTRY_BLOCKER_PLUGIN_DIR . 'includes/fv-country-blocker-updater.php';
    require_once FV_COUNTRY_BLOCKER_PLUGIN_DIR . 'admin/admin-page.php';
  }

  private function define_admin_hooks() {
    add_action('admin_menu', [$this, 'add_admin_menu']);
    add_action('admin_init', [$this, 'register_settings']);
    add_action('fv_country_blocker_update_db', [$this, 'update_geoip_database']);

    //checking for plugin updates
    add_action('admin_init', function () {new GitHubPluginUpdater();});
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
