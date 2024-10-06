<?php
class WP_GitHub_Updater {
  const PLUGIN_SLUG = "fv-country-blocker";
  public function __construct() {
    if (is_admin()) {
      add_filter('site_transient_update_plugins', [$this, 'check_for_plugin_updates']);
      add_filter('upgrader_post_install', array($this, 'upgrader_post_install'), 10, 3);
    }
  }

  function check_for_plugin_updates($transient) {
    if (!$transient) {
      return $transient;
    }

    // Plugin slug and path
    $plugin_file = self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php';

    // Check if the transient is set and still valid (e.g., cached for 12 hours)
    $cached_update = get_transient(self::PLUGIN_SLUG . '_transient_data');

    if ($cached_update) {
      // If the update data is cached, use the cached value
      $transient->response[$plugin_file] = $cached_update;
      return $transient;
    }

    // Proceed with the regular update check
    if (empty($transient->checked)) {
      return $transient;
    }

    //     // Plugin details
    $author = 'nimrod-cohen'; // Replace with your GitHub username
    $plugin_file = self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php';
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
    $current_version = $plugin_data['Version'];

    // GitHub API URL for the latest release
    $github_api_url = 'https://api.github.com/repos/' . $author . '/' . self::PLUGIN_SLUG . '/releases/latest';

    // Make the API request to GitHub
    $response = wp_remote_get($github_api_url);
    if (is_wp_error($response)) {
      return $transient;
    }

    $release_data = json_decode(wp_remote_retrieve_body($response));

    // Check if there's a rate limit or other GitHub error
    //check if message contains 'API rate limit exceeded'
    if (isset($release_data->message) && strpos($release_data->message, 'API rate limit exceeded') !== false) {
      error_log('GitHub API rate limit exceeded');
      $plugin_update = (object) array(
        'slug' => self::PLUGIN_SLUG,
        'new_version' => $current_version,
        'url' => '',
        'package' => ''
      );
      set_transient(self::PLUGIN_SLUG . '_transient_data', $plugin_update, 10 * MINUTE_IN_SECONDS);
      return $transient;
    }

    $repo_version = preg_replace('/^v/', '', $release_data->tag_name);

    if (!version_compare($current_version, $repo_version, '<')) {
      return $transient;
    }

    // Update details
    $plugin_update = (object) array(
      'slug' => self::PLUGIN_SLUG,
      'new_version' => $repo_version,
      'url' => $release_data->html_url,
      'package' => $release_data->zipball_url
    );

    // Cache the update data for 12 hours to prevent repeated checks
    set_transient(self::PLUGIN_SLUG . '_transient_data', $plugin_update, 5 * MINUTE_IN_SECONDS);

    // Add the update to the transient
    $transient->response[$plugin_file] = $plugin_update;

    return $transient;
  }

  public function upgrader_post_install($true, $hook_extra, $result) {

    global $wp_filesystem;

    // Move & Activate
    $proper_destination = WP_PLUGIN_DIR . '/' . self::PLUGIN_SLUG;
    $wp_filesystem->move($result['destination'], $proper_destination);
    $result['destination'] = $proper_destination;
    $activate = activate_plugin(WP_PLUGIN_DIR . '/' . self::PLUGIN_SLUG);

    // Output the update message
    $fail = __('The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'github_plugin_updater');
    $success = __('Plugin reactivated successfully.', 'github_plugin_updater');
    echo is_wp_error($activate) ? $fail : $success;
    return $result;

  }
}

$wpGHUpdater = new WP_GitHub_Updater();