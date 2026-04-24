<?php
defined('ABSPATH') || exit;

class FV_BotDetector {
  const TOR_LIST_URL = 'https://check.torproject.org/torbulkexitlist';
  const DATACENTER_LIST_URL = 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv4.txt';
  const OPT_ENABLE_TOR = 'fv_country_blocker_enable_tor';
  const OPT_ENABLE_DATACENTER = 'fv_country_blocker_enable_datacenter';

  private static $torIps = null;
  private static $datacenterCidrs = null;

  public static function is_tor_enabled() {
    return get_option(self::OPT_ENABLE_TOR, '1') === '1';
  }

  public static function is_datacenter_enabled() {
    return get_option(self::OPT_ENABLE_DATACENTER, '1') === '1';
  }

  public static function isSuspicious($ip) {
    return self::isTor($ip) || self::isDatacenter($ip);
  }

  public static function isTor($ip) {
    if (!self::is_tor_enabled() || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      return false;
    }
    if (self::$torIps === null) {
      self::$torIps = [];
      $file = self::torFile();
      if (is_readable($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
          $line = trim($line);
          if ($line === '' || $line[0] === '#') continue;
          self::$torIps[$line] = true;
        }
      }
    }
    return isset(self::$torIps[$ip]);
  }

  public static function isDatacenter($ip) {
    if (!self::is_datacenter_enabled() || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      return false;
    }
    if (self::$datacenterCidrs === null) {
      self::$datacenterCidrs = [];
      $file = self::datacenterFile();
      if (is_readable($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
          $line = trim($line);
          if ($line === '' || $line[0] === '#') continue;
          if (strpos($line, '/') === false) {
            $line .= '/32';
          }
          [$base, $bits] = explode('/', $line, 2);
          $baseLong = ip2long($base);
          $bits = (int) $bits;
          if ($baseLong === false || $bits < 0 || $bits > 32) continue;
          $mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;
          self::$datacenterCidrs[] = [$baseLong & $mask, $mask];
        }
      }
    }
    $ipLong = ip2long($ip);
    if ($ipLong === false) return false;
    foreach (self::$datacenterCidrs as [$netLong, $mask]) {
      if (($ipLong & $mask) === $netLong) {
        return true;
      }
    }
    return false;
  }

  public static function refreshTorList() {
    return self::downloadTo(self::TOR_LIST_URL, self::torFile(), 'tor');
  }

  public static function refreshDatacenterList() {
    return self::downloadTo(self::DATACENTER_LIST_URL, self::datacenterFile(), 'datacenter');
  }

  public static function registerCron() {
    add_action('fv_botdetector_refresh_tor', [__CLASS__, 'refreshTorList']);
    add_action('fv_botdetector_refresh_datacenter', [__CLASS__, 'refreshDatacenterList']);
    if (!wp_next_scheduled('fv_botdetector_refresh_tor')) {
      wp_schedule_event(time(), 'hourly', 'fv_botdetector_refresh_tor');
    }
    if (!wp_next_scheduled('fv_botdetector_refresh_datacenter')) {
      wp_schedule_event(time(), 'daily', 'fv_botdetector_refresh_datacenter');
    }
  }

  public static function unregisterCron() {
    wp_clear_scheduled_hook('fv_botdetector_refresh_tor');
    wp_clear_scheduled_hook('fv_botdetector_refresh_datacenter');
  }

  private static function listsDir() {
    $upload = wp_upload_dir();
    $dir = $upload['basedir'] . '/bot-lists';
    if (!is_dir($dir)) {
      wp_mkdir_p($dir);
    }
    return $dir;
  }

  private static function torFile() {
    return self::listsDir() . '/tor-exits.txt';
  }

  private static function datacenterFile() {
    return self::listsDir() . '/datacenter-ipv4.txt';
  }

  private static function downloadTo($url, $path, $label) {
    $response = wp_remote_get($url, ['timeout' => 30]);
    if (is_wp_error($response)) {
      error_log("FV_BotDetector: refresh $label failed: " . $response->get_error_message());
      return false;
    }
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    if ($code !== 200 || strlen($body) < 100) {
      error_log("FV_BotDetector: refresh $label bad response: code=$code len=" . strlen($body));
      return false;
    }
    $tmp = $path . '.tmp';
    if (file_put_contents($tmp, $body) === false) {
      error_log("FV_BotDetector: refresh $label write failed to $tmp");
      return false;
    }
    rename($tmp, $path);
    self::$torIps = null;
    self::$datacenterCidrs = null;
    return true;
  }
}
