<?php

class FV_Country_Blocker {

  protected $loader;
  protected $plugin_name;
  protected $version;

  public function __construct() {
    $this->plugin_name = 'fv-country-blocker';
    $this->version = FV_COUNTRY_BLOCKER_VERSION;
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
    add_action('admin_menu', array($this, 'add_admin_menu'));
    add_action('admin_init', array($this, 'register_settings'));
    add_action('fv_country_blocker_update_db', array($this, 'update_geoip_database'));
  }

  private function define_public_hooks() {
    add_action('init', array($this, 'check_visitor_country'));
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
    register_setting('fv_country_blocker_options', 'fv_country_blocker_blocked_countries', array(
      'type' => 'array',
      'sanitize_callback' => array($this, 'sanitize_blocked_countries')
    ));
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
    // Implement country checking logic here
  }

  public function run() {
    if (!wp_next_scheduled('fv_country_blocker_update_db')) {
      wp_schedule_event(time(), 'weekly', 'fv_country_blocker_update_db');
    }
  }
}
