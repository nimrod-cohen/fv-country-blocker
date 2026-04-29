<?php
/**
 * Plugin Name: FV Country Blocker
 * Plugin URI: https://github.com/nimrod-cohen/fv-country-blocker
 * Description: Block visitors from specific countries using MaxMind GeoIP database.
 * Version: 1.5.10
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
    add_action('admin_bar_menu', [$this, 'add_admin_bar_link'], 100);
    add_action('admin_head', [$this, 'admin_bar_icon_styles']);
    add_action('wp_head', [$this, 'admin_bar_icon_styles']);
  }

  public function add_admin_bar_link($wp_admin_bar) {
    if (!current_user_can('manage_options')) return;
    $wp_admin_bar->add_node([
      'id' => 'fv-country-blocker-settings',
      'title' => '<span class="ab-icon dashicons dashicons-shield"></span><span class="ab-label">Country Blocker</span>',
      'href' => admin_url('options-general.php?page=fv-country-blocker'),
      'meta' => ['title' => 'FV Country Blocker settings']
    ]);
  }

  public function admin_bar_icon_styles() {
    if (!is_admin_bar_showing() || !current_user_can('manage_options')) return;
    echo '<style>
      #wpadminbar #wp-admin-bar-fv-country-blocker-settings .ab-icon:before {
        font-family: dashicons !important;
        content: "\f332";
        top: 3px;
      }
    </style>';
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

    self::ensure_tokens_table();

    // Schedule the cron job for database updates
    if (!wp_next_scheduled('fv_country_blocker_update_db')) {
      wp_schedule_event(time(), 'weekly', 'fv_country_blocker_update_db');
    }
  }

  public static function deactivate() {
    // Deactivation code here
    // For example, you might want to clear scheduled events
    wp_clear_scheduled_hook('fv_country_blocker_update_db');
    if (class_exists('FV_BotDetector')) {
      FV_BotDetector::unregisterCron();
    }

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
      $updater = new \FVCountryBlocker\GitHubPluginUpdater(__FILE__);
    });
  }

  private function define_public_hooks() {
    add_action('init', [$this, 'check_visitor_country']);
    add_action('init', ['FV_BotDetector', 'registerCron']);
    add_action('wp_ajax_fv_country_blocker_test_ip', [$this, 'test_ip']);
    add_action('wp_ajax_fv_country_blocker_token_create', [$this, 'ajax_token_create']);
    add_action('wp_ajax_fv_country_blocker_token_revoke', [$this, 'ajax_token_revoke']);
    add_action('wp_ajax_fv_country_blocker_token_list',   [$this, 'ajax_token_list']);
    add_action('admin_init', [__CLASS__, 'ensure_tokens_table']);
  }

  // -------------------------------------------------------------------------
  // Bypass-token admin AJAX
  // -------------------------------------------------------------------------

  private function token_guard() {
    if (!current_user_can('manage_options')) {
      wp_send_json_error('Forbidden', 403);
    }
    if (!wp_verify_nonce($_REQUEST['nonce'] ?? '', 'fv-country-blocker-nonce')) {
      wp_send_json_error('Invalid nonce', 403);
    }
  }

  public function ajax_token_list() {
    $this->token_guard();
    global $wpdb;
    $table = $wpdb->prefix . 'fvcb_bypass_tokens';
    $rows = $wpdb->get_results("SELECT id, token, name, created_at, last_used_at, revoked FROM $table ORDER BY id DESC", ARRAY_A);
    wp_send_json_success(['tokens' => $rows ?: []]);
  }

  public function ajax_token_create() {
    $this->token_guard();
    $name = trim((string) ($_REQUEST['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 120) {
      wp_send_json_error('Name required (1-120 chars)');
    }
    global $wpdb;
    $table = $wpdb->prefix . 'fvcb_bypass_tokens';
    $token = wp_generate_password(24, false);
    $ok = $wpdb->insert($table, [
      'token' => $token,
      'name' => $name,
      'created_at' => current_time('mysql', true),
    ]);
    if (!$ok) wp_send_json_error('insert failed: ' . $wpdb->last_error);
    wp_send_json_success(['id' => $wpdb->insert_id, 'token' => $token, 'name' => $name]);
  }

  public function ajax_token_revoke() {
    $this->token_guard();
    $id = (int) ($_REQUEST['id'] ?? 0);
    if (!$id) wp_send_json_error('id required');
    global $wpdb;
    $table = $wpdb->prefix . 'fvcb_bypass_tokens';
    $wpdb->update($table, ['revoked' => 1], ['id' => $id]);
    wp_send_json_success(['id' => $id]);
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
    $is_tor = class_exists('FV_BotDetector') && FV_BotDetector::isTor($ip);
    $is_datacenter = class_exists('FV_BotDetector') && FV_BotDetector::isDatacenter($ip);
    $is_apple_pr = class_exists('FV_BotDetector') && FV_BotDetector::isApplePrivateRelay($ip);
    $blocked_countries = get_option('fv_country_blocker_blocked_countries', []);
    if (!is_array($blocked_countries)) {
      $blocked_countries = explode(',', $blocked_countries);
    }
    $country_blocked = $country && in_array($country, $blocked_countries, true);

    wp_send_json_success([
      'country' => $country ?: null,
      'is_tor' => $is_tor,
      'is_datacenter' => $is_datacenter,
      'is_apple_private_relay' => $is_apple_pr,
      'country_blocked' => $country_blocked,
      'would_block' => $country_blocked || $is_tor || $is_datacenter,
    ]);
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

  public static function is_whitelisted_ip($ip) {
    if (empty($ip)) return false;

    // Server's own IPs are always allowed — both the interface that received
    // the request (SERVER_ADDR) and the server's external/public IP (for
    // server-to-self calls that loop out to the public hostname).
    if ($ip === ($_SERVER['SERVER_ADDR'] ?? '')) return true;
    if ($ip === self::get_server_external_ip()) return true;

    $raw = get_option('fv_country_blocker_whitelisted_ips', '');
    if (empty($raw)) return false;

    $list = array_filter(array_map('trim', preg_split('/[\s,]+/', $raw)));
    return in_array($ip, $list, true);
  }

  /**
   * Bypass-token check. Tokens are stored in wp_fvcb_bypass_tokens with
   * name + created_at + last_used_at + revoked. Visitors arriving with
   * `?fv_bypass=<token>` (matching a non-revoked row) skip all blocking
   * and get a 10-year cookie. Subsequent visits with that cookie also
   * skip blocking. Useful for legitimate users on filtered/proxied
   * networks (e.g. NetFree) whose IPs are flagged as datacenter.
   */
  public static function check_bypass_token() {
    $candidate = isset($_GET['fv_bypass']) ? (string) $_GET['fv_bypass'] : '';
    $from_url = $candidate !== '';
    if (!$candidate) {
      $candidate = isset($_COOKIE['fv_bypass']) ? (string) $_COOKIE['fv_bypass'] : '';
    }
    if ($candidate === '' || strlen($candidate) > 64) return false;

    global $wpdb;
    $table = $wpdb->prefix . 'fvcb_bypass_tokens';
    $row = $wpdb->get_row($wpdb->prepare(
      "SELECT id, token, last_used_at FROM $table WHERE token = %s AND revoked = 0 LIMIT 1",
      $candidate
    ), ARRAY_A);
    if (!$row) return false;

    if ($from_url) {
      // Persist as cookie so the URL param isn't needed again.
      setcookie('fv_bypass', $row['token'], [
        'expires' => time() + (10 * YEAR_IN_SECONDS),
        'path' => '/',
        'secure' => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
      ]);
    }

    // Throttle last_used_at updates to once per minute to avoid hot writes.
    $last = $row['last_used_at'] ? strtotime($row['last_used_at']) : 0;
    if ((time() - $last) > 60) {
      $wpdb->update($table, ['last_used_at' => current_time('mysql', true)], ['id' => $row['id']]);
    }

    return true;
  }

  public static function ensure_tokens_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'fvcb_bypass_tokens';
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE $table (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      token VARCHAR(64) NOT NULL,
      name VARCHAR(120) NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      last_used_at DATETIME NULL,
      revoked TINYINT(1) NOT NULL DEFAULT 0,
      PRIMARY KEY (id),
      UNIQUE KEY token (token),
      KEY revoked (revoked)
    ) $charset;");
  }

  public static function is_trusted_user_agent($ua) {
    if (empty($ua)) return false;

    $raw = get_option('fv_country_blocker_trusted_user_agents', '');
    if (empty($raw)) return false;

    foreach (preg_split('/\r?\n/', $raw) as $pattern) {
      $pattern = trim($pattern);
      if ($pattern !== '' && stripos($ua, $pattern) !== false) {
        return true;
      }
    }
    return false;
  }

  public static function get_server_external_ip() {
    $cached = get_transient('fv_country_blocker_server_external_ip');
    if ($cached !== false) return $cached;

    $response = wp_remote_get('https://api.ipify.org', ['timeout' => 3]);
    $ip = '';
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
      $ip = trim(wp_remote_retrieve_body($response));
      if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '';
      }
    }
    // Cache either the resolved IP (24h) or a failure marker (5min) to avoid
    // hammering ipify on every request.
    set_transient('fv_country_blocker_server_external_ip', $ip, $ip ? DAY_IN_SECONDS : 5 * MINUTE_IN_SECONDS);
    return $ip;
  }

  public function check_visitor_country() {
    $force = $_GET["force_country_ip"] ?? false;

    // Get the user's IP address
    $ip = FV_GeoIP::get_user_ip();

    // Bypass token (URL ?fv_bypass=… or persistent cookie). Sets the cookie
    // on first hit so subsequent visits skip the whole block path.
    if (self::check_bypass_token()) {
      return;
    }

    if (!$force && (
      current_user_can('administrator')
      || wp_get_environment_type() != 'production'
      || $ip == '127.0.0.1'
      || self::is_whitelisted_ip($ip)
      || self::is_trusted_user_agent($_SERVER['HTTP_USER_AGENT'] ?? ''))) {
      return;
    }

    if ($force && $ip == '127.0.0.1') {
      $ip = $force;
    }

    // Tor / datacenter block — each toggle gates its own list via FV_BotDetector.
    if (class_exists('FV_BotDetector')) {
      if (FV_BotDetector::isTor($ip)) {
        self::send_block_response('tor', $ip);
      }
      if (FV_BotDetector::isDatacenter($ip)) {
        self::send_block_response('datacenter', $ip);
      }
    }

    $visitor_country = FV_GeoIP::get_visitor_country($ip);
    if (!$visitor_country) {
      error_log('FV Country Blocker: Could not determine visitor country.');
      return;
    }

    $blocked = get_option('fv_country_blocker_blocked_countries', []);

    if (in_array($visitor_country, $blocked)) {
      self::send_block_response('country:' . $visitor_country, $ip);
    }

    //all good.
  }

  private static function send_block_response($reason, $ip) {
    status_header(403);
    $html = get_option('fv_country_blocker_custom_blocking_html', '');
    if ($html === '') {
      $html = '<h1>Access Denied</h1><p>Sorry, this site is not available from your location.</p>';
    }
    $ts = gmdate('Y-m-d H:i:s') . ' UTC';
    $html .= '<div style="position:fixed;bottom:0;left:0;right:0;padding:8px 12px;background:#f5f5f5;color:#666;font-family:monospace;font-size:11px;text-align:center;border-top:1px solid #ddd">Reference: '
      . esc_html($ts) . ' &middot; ' . esc_html($ip) . ' &middot; ' . esc_html($reason) . '</div>';
    die($html);
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