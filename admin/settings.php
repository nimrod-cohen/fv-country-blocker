<div id="settings" class="tab-content">
    <h2>MaxMind MMDB Settings</h2>
    <table class="form-table">
      <tr valign="top">
          <th scope="row">MaxMind License Key</th>
          <td>
            <input type="text" name="fv_country_blocker_license_key" class="maxmind-input" value="<?php echo esc_attr(get_option('fv_country_blocker_license_key')); ?>" />
            <p class="description">This is only required if the custom path setting is empty, and you need to download the mmdb file regularly for this WP install independently</p>
          </td>
      </tr>
      <tr valign="top">
          <th scope="row">Custom MMDB File Path</th>
          <td>
              <input type="text" name="fv_country_blocker_custom_mmdb_path" value="<?php echo esc_attr($custom_mmdb_path); ?>" class="maxmind-input" />
              <p class="description">Leave empty to use the current WP installation path. if you have multiple WP installs, and want to use the same MMDB for all of them, you can use the custom path, it is then your responsibility to make sure that the file is downloaded regularly.</p>
          </td>
      </tr>
      <tr valign="top">
            <th scope="row">Custom Blocking HTML</th>
            <td>
                <textarea name="fv_country_blocker_custom_blocking_html" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_blocking_html); ?></textarea>
                <p class="description">Enter the HTML to be displayed when a visit is blocked.</p>
            </td>
      </tr>
      <tr valign="top">
        <th scope="row">Last Update</th>
        <td><?php echo $last_update; ?></td>
      </tr>
      <tr valign="top">
        <th scope="row">Custom User IP header</th>
        <td>
          <input type="text" name="fv_country_blocker_custom_user_ip_header" value="<?php echo esc_attr(get_option('fv_country_blocker_custom_user_ip_header')); ?>" />
          <p class="description">Your current IP is <?php echo FV_GeoIP::get_user_ip(); ?><br/>If you are behind a proxy/CDN, you can set a custom user IP header here. Leave empty to use the defaults</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Whitelisted IPs</th>
        <td>
          <textarea name="fv_country_blocker_whitelisted_ips" rows="4" cols="50" class="large-text code"><?php echo esc_textarea(get_option('fv_country_blocker_whitelisted_ips', '')); ?></textarea>
          <p class="description">One IP per line (or comma-separated). These IPs bypass all blocking checks. The server's own IPs are always whitelisted: internal <code><?php echo esc_html($_SERVER['SERVER_ADDR'] ?? 'unknown'); ?></code>, external <code><?php echo esc_html(FV_Country_Blocker::get_server_external_ip() ?: 'unresolved'); ?></code>.</p>
        </td>
      </tr>
    </table>

</div>
