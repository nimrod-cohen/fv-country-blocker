<div id="countries" class="tab-content" style="display:none;">
    <?php $country_on = get_option('fv_country_blocker_country_enabled', '1') === '1'; ?>
    <div class="fvcb-section-toggle" style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
      <label class="fvcb-switch">
        <input type="checkbox" class="fvcb-section-checkbox" data-section="country" <?php checked($country_on); ?>>
        <span class="fvcb-slider"></span>
      </label>
      <span class="fvcb-section-label" style="font-weight:600;">Country blocking is <span class="fvcb-state-text"><?php echo $country_on ? 'ON' : 'OFF'; ?></span></span>
    </div>
    <h2>Select countries to block</h2>
    <input type="text" id="country-search" placeholder="Search countries..." style="margin-bottom: 10px; width: 100%; max-width: 400px;">
    <div class="fv-country-list" style="max-height: 400px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px;">
        <?php foreach ($countries as $code => $names): ?>
            <label class="country-item"
              data-code="<?php echo esc_attr(strtolower($code)); ?>"
              data-name="<?php echo esc_attr(strtolower($names["name"])); ?>"
              title="<?php echo esc_attr(strtolower($names["long_name"])); ?>"
              data-long-name="<?php echo esc_attr(strtolower($names["long_name"])); ?>">
              <input type="checkbox" name="blocked_countries[]" value="<?php echo esc_attr($code); ?>"
              <?php checked(in_array($code, $blocked_countries));?>>
              <img src="<?php echo esc_url(fv_country_blocker_get_flag_url($code)); ?>"
                alt="<?php echo esc_attr($names["name"]); ?> flag"
                class="flag"><?php echo esc_html($names["name"]); ?></label>
        <?php endforeach;?>
    </div>
</div>
