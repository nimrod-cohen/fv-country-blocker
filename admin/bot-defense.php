<div id="bot-defense" class="tab-content" style="display:none;">
    <?php $bot_on = get_option('fv_country_blocker_bot_defense_enabled', '1') === '1'; ?>
    <div class="fvcb-section-toggle" style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
      <label class="fvcb-switch">
        <input type="checkbox" class="fvcb-section-checkbox" data-section="bot" <?php checked($bot_on); ?>>
        <span class="fvcb-slider"></span>
      </label>
      <span class="fvcb-section-label" style="font-weight:600;">Bot defense is <span class="fvcb-state-text"><?php echo $bot_on ? 'ON' : 'OFF'; ?></span></span>
    </div>
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
          <div style="margin-top:8px;padding-left:24px;border-left:2px solid #e0e0e0">
            <label><input type="checkbox" name="fv_country_blocker_allow_apple_private_relay" value="1" <?php checked(get_option('fv_country_blocker_allow_apple_private_relay', '1'), '1'); ?> /> Allow Apple iCloud Private Relay</label>
            <p class="description">Apple Private Relay routes iPhone/Mac users through Akamai infrastructure that overlaps the datacenter list. Enabling this exempts Apple's published egress range so real Apple users aren't blocked. Auto-refreshed daily from <code>mask-api.icloud.com</code>.</p>
          </div>
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
