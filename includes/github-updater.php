<?php

declare (strict_types = 1);

/**
 * Update WordPress plugin from GitHub Private Repository.
 */
class GhPluginUpdater {
  private $file;
  private $plugin_data;
  private $basename;
  private $active = false;
  private $github_response;
  private $github_auth_token = null;
  private $github_username = null;
  private $github_repository = null;
  private $slug = null;

  public function __construct($file, $github_auth_token = null, $github_username = null, $github_repository = null) {
    $this->file = $file;
    $this->basename = plugin_basename($this->file);
    $this->slug = current(explode('/', $this->basename));
    $this->github_auth_token = $github_auth_token;
    $this->github_username = $github_username;
    $this->github_repository = $github_repository;
  }

  /**
   * Init GitHub Plugin Updater.
   */
  public function init(): void {
    add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient'], 10, 1);
    add_filter('http_request_args', [$this, 'set_header_token'], 10, 2);
    add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
    add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
  }

  /**
   * If new version exists, update transient with GitHub info.
   *
   * @param object $transient Transient object with plugins information.
   */
  public function modify_transient(object $transient): object {
    if (!property_exists($transient, 'checked')) {
      return $transient;
    }

    $this->get_repository_info();
    $this->get_plugin_data();

    if (version_compare($this->github_response['tag_name'], $transient->checked[$this->basename], 'gt')) {
      $plugin = [
        'url' => $this->plugin_data['PluginURI'],
        'slug' => $this->slug,
        'package' => $this->github_response['zipball_url'],
        'new_version' => $this->github_response['tag_name']
      ];

      $transient->response[$this->basename] = (object) $plugin;
    }

    return $transient;
  }

  /**
   * Complete details of new plugin version on popup.
   *
   * @param array|false|object $result The result object or array. Default false.
   * @param string             $action The type of information being requested from the Plugin Installation API.
   * @param object             $args   Plugin API arguments.
   */
  public function plugin_popup(bool $result, string $action, object $args) {
    if ('plugin_information' !== $action || empty($args->slug)) {
      return false;
    }

    if ($args->slug == $this->slug) {
      $this->get_repository_info();
      $this->get_plugin_data();

      $plugin = [
        'name' => $this->plugin_data['Name'],
        'slug' => $this->basename,
        'requires' => $this->plugin_data['RequiresWP'],
        'tested' => $this->plugin_data['TestedUpTo'],
        'version' => $this->github_response['tag_name'],
        'author' => $this->plugin_data['AuthorName'],
        'author_profile' => $this->plugin_data['AuthorURI'],
        'last_updated' => $this->github_response['published_at'],
        'homepage' => $this->plugin_data['PluginURI'],
        'short_description' => $this->plugin_data['Description'],
        'sections' => [
          'Description' => $this->plugin_data['Description'],
          'Updates' => $this->github_response['body']
        ],
        'download_link' => $this->github_response['zipball_url']
      ];

      return (object) $plugin;
    }

    return $result;
  }

  /**
   * Active plugin after install new version.
   *
   * @param bool  $response   Installation response.
   * @param array $hook_extra Extra arguments passed to hooked filters.
   * @param array $result     Installation result data.
   */
  public function after_install(bool $response, array $hook_extra, array $result): bool {
    global $wp_filesystem;

    $install_directory = plugin_dir_path($this->file);
    $wp_filesystem->move($result['destination'], $install_directory);
    $result['destination'] = $install_directory;

    if ($this->active) {
      activate_plugin($this->basename);
    }

    return $response;
  }

  /**
   * GitHub access_token param was deprecated. We need to set header with token for requests.
   *
   * @param array  $args HTTP request arguments.
   * @param string $url  The request URL.
   */
  public function set_header_token(array $parsed_args, string $url): array {
    $parsed_url = parse_url($url);

    if ('api.github.com' === ($parsed_url['host'] ?? null) && isset($parsed_url['query'])) {
      parse_str($parsed_url['query'], $query);

      if (isset($query['access_token'])) {
        $parsed_args['headers']['Authorization'] = 'token ' . $query['access_token'];

        $this->active = is_plugin_active($this->basename);
      }
    }

    return $parsed_args;
  }

  /**
   * Gets repository data from GitHub.
   */
  private function get_repository_info(): void {
    if (null !== $this->github_response) {
      return;
    }

    $transient = get_transient('gh_latest_release');
    if ($transient) {
      $this->github_response = $transient;
      return;
    }

    $args = [
      'method' => 'GET',
      'timeout' => 5,
      'redirection' => 5,
      'httpversion' => '1.0',
      'sslverify' => true
    ];

    if ($this->github_auth_token) {
      $args['headers'] = [
        'Authorization' => 'token ' . $this->github_auth_token
      ];
    }

    $request_uri = sprintf("https://api.github.com/repos/%s/%s/releases/latest", $this->github_username, $this->github_repository);

    $request = wp_remote_get($request_uri, $args);
    $response = json_decode(wp_remote_retrieve_body($request), true);

    if ($this->github_auth_token) {
      $response['zipball_url'] = add_query_arg('access_token', $this->github_auth_token, $response['zipball_url']);
    }

    $response['tag_name'] = str_replace('v', '', $response['tag_name']);

    set_transient('gh_latest_release', $response, 5 * MINUTE_IN_SECONDS);
    $this->github_response = $response;
  }

  /**
   * Gets plugin data.
   */
  private function get_plugin_data(): void {
    if (null !== $this->plugin_data) {
      return;
    }

    $this->plugin_data = get_plugin_data($this->file);
  }
}