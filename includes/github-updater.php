<?php
/*
 * Plugin name: Misha Update Checker
 * Description: This simple plugin does nothing, only gets updates from a custom server
 * Version: 1.1
 * Author: Misha Rudrastyh, changes by Nimrod Cohen
 * Author URI: https://rudrastyh.com
 * License: GPL
 *
 * Make sure to set Author to your github user handle and Version in the plugin header
 */

/**/

defined('ABSPATH') || exit;

if (!class_exists('GitHubPluginUpdater')) {

  class GitHubPluginUpdater {
    const PLUGIN_SLUG = "fv-country-blocker";

    private $plugin_slug;
    private $version;
    private $cache_key;
    private $author;
    private $cache_allowed;
    private $latest_release = null;
    private $plugin_file = null;

    private function get_plugin_details() {
      $this->plugin_file = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
      $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_file);
      $this->version = $plugin_data['Version'];
      $this->author = $plugin_data['AuthorName'];
    }

    public function __construct() {
      $file = __FILE__;
      $this->plugin_slug = explode('/', plugin_basename($file))[0];
      $this->cache_key = $this->plugin_slug . '_transient_data';
      $this->cache_allowed = false;
      $this->get_plugin_details();

      add_filter('plugins_api', [$this, 'info'], 20, 3);
      add_filter('site_transient_update_plugins', [$this, 'update']);
      add_action('upgrader_process_complete', [$this, 'purge'], 10, 2);
      add_action('upgrader_post_install', [$this, 'post_install'], 10, 3);
    }

    public function request() {

      $remote = get_transient($this->cache_key);

      if (false === $remote || !$this->cache_allowed) {

        $url = 'https://raw.githubusercontent.com/' . $this->author . '/' . $this->plugin_slug . '/' . $this->latest_release->tag_name . '/release.json';

        $remote = wp_remote_get(
          $url,
          [
            'timeout' => 10,
            'headers' => [
              'Accept' => 'application/json'
            ]
          ]
        );

        if (
          is_wp_error($remote)
          || 200 !== wp_remote_retrieve_response_code($remote)
          || empty(wp_remote_retrieve_body($remote))
        ) {
          return false;
        }

        set_transient($this->cache_key, $remote, 5 * MINUTE_IN_SECONDS);
      }

      $remote = json_decode(wp_remote_retrieve_body($remote));

      return $remote;

    }

    function info($res, $action, $args) {
      // do nothing if you're not getting plugin information right now
      if ('plugin_information' !== $action) {
        return $res;
      }

      // do nothing if it is not our plugin
      if ($this->plugin_slug !== $args->slug) {
        return $res;
      }

      // get updates
      $remote = $this->request();

      if (!$remote) {
        return $res;
      }

      $res = new stdClass();

      $res->name = $remote->name;
      $res->slug = $remote->slug;
      $res->version = $remote->version;
      $res->tested = $remote->tested;
      $res->requires = $remote->requires;
      $res->author = $remote->author;
      $res->author_profile = $remote->author_profile;
      $res->download_link = $remote->download_url;
      $res->trunk = $remote->download_url;
      $res->requires_php = $remote->requires_php;
      $res->last_updated = $remote->last_updated;

      $res->sections = array(
        'description' => $remote->sections->description,
        'installation' => $remote->sections->installation,
        'changelog' => $remote->sections->changelog
      );

      if (!empty($remote->banners)) {
        $res->banners = array(
          'low' => $remote->banners->low,
          'high' => $remote->banners->high
        );
      }

      return $res;

    }

    private function get_latest_release() {
      if ($this->latest_release) {
        return true;
      }

      $transient = get_transient($this->plugin_slug . '_latest_release');
      if ($transient) {
        $this->latest_release = $transient;
        return true;
      }

      $github_api_url = 'https://api.github.com/repos/' . $this->author . '/' . $this->plugin_slug . '/releases/latest';

      // Make the API request to GitHub
      $response = wp_remote_get($github_api_url);
      if (is_wp_error($response)) {
        return false;
      }

      $this->latest_release = json_decode(wp_remote_retrieve_body($response));

      set_transient($this->plugin_slug . '_latest_release', $this->latest_release, 5 * MINUTE_IN_SECONDS);

      return true;
    }

    public function update($transient) {

      if (empty($transient->checked)) {
        return $transient;
      }

      // GitHub API URL for the latest release
      if (!$this->get_latest_release()) {
        return $transient;
      }

      $remote = $this->request();

      if (
        $remote
        && version_compare($this->version, $remote->version, '<')
        && version_compare($remote->requires, get_bloginfo('version'), '<=')
        && version_compare($remote->requires_php, PHP_VERSION, '<')
      ) {
        $res = new stdClass();
        $res->slug = $this->plugin_slug;
        $res->plugin = $this->plugin_file; // misha-update-plugin/misha-update-plugin.php
        $res->new_version = $remote->version;
        $res->tested = $remote->tested;

        $res->package = $this->latest_release->zipball_url;

        $transient->response[$res->plugin] = $res;

      }

      return $transient;

    }

    public function purge($upgrader, $options) {

      if (
        $this->cache_allowed
        && 'update' === $options['action']
        && 'plugin' === $options['type']
      ) {
        // just clean the cache when new plugin version is installed
        delete_transient($this->cache_key);
        delete_transient($this->plugin_slug . '_latest_release');
      }
    }

    public function post_install($true, $hook_extra, $result) {
      global $wp_filesystem;

      // Move to proper destination
      $proper_destination = WP_PLUGIN_DIR . '/' . $this->plugin_slug;
      $wp_filesystem->move($result['destination'], $proper_destination);
      $result['destination'] = $proper_destination;
      return $result;
    }

  }
}