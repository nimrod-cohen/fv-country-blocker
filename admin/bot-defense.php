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
          <div style="margin-top:8px;padding-left:24px;border-left:2px solid #e0e0e0">
            <label><input type="checkbox" name="fv_country_blocker_allow_apple_private_relay" value="1" <?php checked(get_option('fv_country_blocker_allow_apple_private_relay', '1'), '1'); ?> /> Allow Apple iCloud Private Relay</label>
            <p class="description">Apple Private Relay routes iPhone/Mac users through Akamai infrastructure that overlaps the datacenter list. Enabling this exempts Apple's published egress range so real Apple users aren't blocked. Auto-refreshed daily from <code>mask-api.icloud.com</code>.</p>
          </div>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Allow known crawlers</th>
        <td>
          <label><input type="checkbox" name="fv_country_blocker_allow_known_crawlers" value="1" <?php checked(get_option('fv_country_blocker_allow_known_crawlers', '1'), '1'); ?> /> Enabled</label>
          <p class="description">Exempts genuine search-engine crawlers from <strong>all</strong> blocking (datacenter, Tor and country) using <strong>forward-confirmed reverse DNS</strong> &mdash; not a spoofable User-Agent. Covers <code>Googlebot</code> (googlebot.com / google.com), <code>bingbot</code> (search.msn.com), Yahoo <code>Slurp</code> (crawl.yahoo.net) and <code>Applebot</code> (applebot.apple.com). DNS lookups run only for requests claiming these crawlers and the result is cached per IP, so legitimate visitors are unaffected. When enabled, a plain <em>Trusted User-Agent</em> entry for these four is ignored (the rDNS check is authoritative). Social crawlers (DuckDuckBot, Twitterbot, facebookexternalhit) don't publish reverse DNS &mdash; add those to <em>Trusted User-Agents</em> if needed.</p>
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
