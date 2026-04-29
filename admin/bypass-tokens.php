<div id="bypass-tokens" class="tab-content">
  <h2>Bypass Tokens</h2>
  <p style="max-width:780px; color:#555;">
    A visitor arriving at any URL with <code>?fv_bypass=&lt;token&gt;</code> (matching a non-revoked token below) skips all blocking and gets a 10-year <code>fv_bypass</code> cookie. Subsequent visits with that cookie also skip blocking. Useful for legitimate users on filtered/proxied networks (e.g. NetFree) whose IPs would otherwise be flagged as datacenter.
    Revoking a token immediately invalidates both URL params and existing cookies.
  </p>

  <div style="display:flex; gap:12px; align-items:center; margin:16px 0;">
    <input type="text" id="fvcb-token-name" placeholder="Token label (e.g. NetFree users)" style="flex:1; max-width:320px; padding:6px 10px;">
    <button type="button" class="button button-primary" id="fvcb-token-create">Create new token</button>
    <span class="fvcb-token-status" style="color:#888;"></span>
  </div>

  <table class="widefat striped fvcb-tokens">
    <thead>
      <tr>
        <th style="width:18%;">Name</th>
        <th>Token</th>
        <th style="width:14%;">Created</th>
        <th style="width:14%;">Last used</th>
        <th style="width:8%;">Status</th>
        <th style="width:14%;">Sample URL</th>
        <th style="width:8%;"></th>
      </tr>
    </thead>
    <tbody>
      <tr><td colspan="7" style="color:#888;">Loading…</td></tr>
    </tbody>
  </table>
</div>
