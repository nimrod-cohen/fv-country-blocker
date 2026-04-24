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
    </table>

    <h2>Bot Defense</h2>
    <p class="description">Reusable helpers for public forms (<code>FV_BotDetector::isSuspicious($ip)</code>, <code>FV_Captcha::render()</code>, <code>FV_Captcha::verify_from_post()</code>). Disabling a feature makes its helpers no-op globally.</p>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Block Tor exit IPs</th>
        <td>
          <label><input type="checkbox" name="fv_country_blocker_enable_tor" value="1" <?php checked(get_option('fv_country_blocker_enable_tor', '1'), '1'); ?> /> Enabled</label>
          <p class="description">Maintains an hourly-refreshed list of Tor exit relays. When disabled, <code>FV_BotDetector::isTor()</code> returns false.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Block datacenter / VPN IPs</th>
        <td>
          <label><input type="checkbox" name="fv_country_blocker_enable_datacenter" value="1" <?php checked(get_option('fv_country_blocker_enable_datacenter', '1'), '1'); ?> /> Enabled</label>
          <p class="description">Maintains a daily-refreshed X4BNet datacenter/VPN CIDR list. When disabled, <code>FV_BotDetector::isDatacenter()</code> returns false.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">CAPTCHA for public forms</th>
        <td>
          <label><input type="checkbox" name="fv_country_blocker_enable_captcha" value="1" <?php checked(get_option('fv_country_blocker_enable_captcha', '1'), '1'); ?> /> Enabled</label>
          <p class="description">When disabled, <code>FV_Captcha::render()</code> produces no output and <code>FV_Captcha::verify_from_post()</code> always succeeds.</p>
        </td>
      </tr>
    </table>
</div>
