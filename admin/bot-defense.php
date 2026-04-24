<div id="bot-defense" class="tab-content" style="display:none;">
    <h2>Bot Defense</h2>
    <p class="description">Enabled checks site-block matching visitors with the same 403 + custom HTML used for blocked countries.</p>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Block Tor exit IPs</th>
        <td>
          <label><input type="checkbox" name="fv_country_blocker_enable_tor" value="1" <?php checked(get_option('fv_country_blocker_enable_tor', '1'), '1'); ?> /> Enabled</label>
          <p class="description">Maintains an hourly-refreshed list of Tor exit relays. Matching visitors get 403.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Block datacenter / VPN IPs</th>
        <td>
          <label><input type="checkbox" name="fv_country_blocker_enable_datacenter" value="1" <?php checked(get_option('fv_country_blocker_enable_datacenter', '1'), '1'); ?> /> Enabled</label>
          <p class="description">Maintains a daily-refreshed X4BNet datacenter/VPN CIDR list. Matching visitors get 403.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">CAPTCHA for public forms</th>
        <td>
          <label><input type="checkbox" name="fv_country_blocker_enable_captcha" value="1" <?php checked(get_option('fv_country_blocker_enable_captcha', '1'), '1'); ?> /> Enabled</label>
          <p class="description">When disabled, <code>FV_Captcha::render()</code> produces no output and <code>FV_Captcha::verify_from_post()</code> always succeeds. Does not affect the Tor/datacenter site-block above.</p>
        </td>
      </tr>
    </table>
</div>
