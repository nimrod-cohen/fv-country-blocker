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
    return $upload_dir . 'GeoLite2-Country.mmdb';
  }

  private function extract_tar_gz($tar_gz_file, $target_dir) {
    // First, decompress the .tar.gz file to a .tar file
    $phar = new PharData($tar_gz_file);
    //check if tar file exists and unlink it
    $tar_file = str_replace('.gz', '', $tar_gz_file); // Get the path of the .tar file
    if (file_exists($tar_file)) {
      unlink($tar_file);
    }

    // Extract the .tar file contents to the target directory
    $phar->decompress(); // This creates a .tar file without the .gz extension

    // Extract the contents of the .tar file
    $phar = new PharData($tar_file);
    $phar->extractTo($target_dir); // Extract the .tar file contents to the target directory

    return true; // Success
  }

  private function clean_upload_folder() {
    //delete all folders under the target_dir
    $files = glob(self::get_upload_dir() . '/*', GLOB_ONLYDIR);
    foreach ($files as $dir) {
      $this->delete_directory($dir);
    }
  }

  private function delete_directory($dir) {
    // Check if the directory exists
    if (!is_dir($dir)) {
      return false;
    }

    // Get the contents of the directory
    $files = array_diff(scandir($dir), array('.', '..'));

    // Loop through each file/folder and delete it
    foreach ($files as $file) {
      $filePath = $dir . DIRECTORY_SEPARATOR . $file;

      // If it's a directory, call this function recursively
      if (is_dir($filePath)) {
        $this->delete_directory($filePath);
      } else {
        // If it's a file, delete it
        unlink($filePath);
      }
    }

    // Now remove the main directory
    return rmdir($dir);
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

      $this->clean_upload_folder();

      $url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=' . $license_key . '&suffix=tar.gz';
      $tmp_file = download_url($url);

      if (is_wp_error($tmp_file)) {
        throw new Exception('Failed to download GeoIP database: ' . $tmp_file->get_error_message());
      }

      $target_dir = self::get_upload_dir();

      if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);

        if (!file_exists($target_dir)) {
          throw new Exception('Failed to create upload directory.');
        }
      }

      $this->extract_tar_gz($tmp_file, $target_dir);
      unlink($tmp_file);

      //find the mmdb file and move it to the correct location
      $files = glob($target_dir . '/**/*.mmdb');
      if (empty($files)) {
        throw new Exception('Failed to extract GeoIP database.');
      }
      //move the file to the $target_dir
      $mmdb_file = $files[0];
      rename($mmdb_file, self::get_mmdb_path());
      //delete all folders under the target_dir
      $this->clean_upload_folder();

      return true;

    } catch (Exception $e) {
      error_log('FV Country Blocker: ' . $e->getMessage());
      return false;
    }
  }
}
