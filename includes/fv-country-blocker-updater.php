<?php

class FV_Country_Blocker_Updater {

  public function update_database() {
    $license_key = get_option('fv_country_blocker_license_key');
    if (empty($license_key)) {
      return false;
    }

    $url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . $license_key . '&suffix=tar.gz';
    $tmp_file = download_url($url);

    if (is_wp_error($tmp_file)) {
      return false;
    }

    WP_Filesystem();
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/fv-country-blocker/';

    if (!file_exists($target_dir)) {
      mkdir($target_dir, 0755, true);
    }

    $unzipped = unzip_file($tmp_file, $target_dir);
    unlink($tmp_file);

    if ($unzipped) {
      update_option('fv_country_blocker_last_update', current_time('mysql'));
      return true;
    }

    return false;
  }
}
