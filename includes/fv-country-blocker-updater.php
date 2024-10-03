<?php

class FV_Country_Blocker_Updater {

  public static function get_upload_dir() {
    WP_Filesystem();
    $upload_dir = wp_upload_dir();
    return $upload_dir['basedir'] . '/fv-country-blocker/';
  }

  public static function get_mmdb_path() {
    $custom_path = get_option('fv_country_blocker_custom_mmdb_path', false);
    if ($custom_path) {
      return $custom_path;
    }

    $upload_dir = self::get_upload_dir();
    return $upload_dir . 'GeoLite2-City.mmdb';
  }

  public function update_database() {
    if (get_option('fv_country_blocker_custom_mmdb_path', false)) {
      //no need to download the database
      return;
    }

    $license_key = get_option('fv_country_blocker_license_key');
    try {
      if (empty($license_key)) {
        throw new Exception('MaxMind license key is missing.');
      }

      $url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . $license_key . '&suffix=tar.gz';
      $tmp_file = download_url($url);

      if (is_wp_error($tmp_file)) {
        throw new Exception('Failed to download GeoIP database: ' . $tmp_file->get_error_message());
      }

      $target_dir = self::get_upload_dir();

      if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
      }
      if (!file_exists($target_dir)) {
        throw new Exception('Failed to create upload directory.');
      }

      $unzipped = unzip_file($tmp_file, $target_dir);
      unlink($tmp_file);

      if (!$unzipped) {
        throw new Exception('Failed to extract GeoIP database.');
      }

      return true;

    } catch (Exception $e) {
      error_log('FV Country Blocker: ' . $e->getMessage());
      return false;
    }
  }
}
