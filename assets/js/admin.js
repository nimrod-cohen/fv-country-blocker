// -----------------------------------------------------------------------------
// Modal dialog — replaces native confirm/alert (which are ugly + browser-styled).
// Promise-based: const ok = await fvcbDialog.confirm('Sure?'); fvcbDialog.alert('Done');
// Supports HTML in message (already-escaped by callers where needed).
// -----------------------------------------------------------------------------
const fvcbDialog = (() => {
  const ensureRoot = () => {
    let root = document.getElementById('fvcb-dialog-root');
    if (!root) {
      root = document.createElement('div');
      root.id = 'fvcb-dialog-root';
      document.body.appendChild(root);
    }
    return root;
  };

  const open = ({ title = '', message = '', buttons }) => new Promise(resolve => {
    const root = ensureRoot();
    const html = `
      <div class="fvcb-dialog-backdrop" role="dialog" aria-modal="true">
        <div class="fvcb-dialog">
          ${title ? `<h3>${title}</h3>` : ''}
          <div class="fvcb-dialog-body">${message}</div>
          <div class="fvcb-dialog-actions">
            ${buttons.map((b, i) => `<button class="button ${b.cls || ''}" data-i="${i}">${b.label}</button>`).join('')}
          </div>
        </div>
      </div>`;
    root.innerHTML = html;

    const close = result => {
      root.innerHTML = '';
      document.removeEventListener('keydown', onKey);
      resolve(result);
    };

    root.querySelectorAll('button[data-i]').forEach(btn => {
      btn.addEventListener('click', () => close(buttons[+btn.dataset.i].result));
    });
    // Click the backdrop to dismiss = same as cancel.
    root.querySelector('.fvcb-dialog-backdrop').addEventListener('click', e => {
      if (e.target === e.currentTarget) close(false);
    });
    const onKey = e => {
      if (e.key === 'Escape') close(false);
      else if (e.key === 'Enter') {
        const primary = root.querySelector('button.button-primary, button.button:last-child');
        if (primary) primary.click();
      }
    };
    document.addEventListener('keydown', onKey);
    setTimeout(() => {
      const focus = root.querySelector('button.button-primary') || root.querySelector('button[data-i]');
      if (focus) focus.focus();
    }, 0);
  });

  return {
    confirm: (message, title = '') => open({
      title, message,
      buttons: [
        { label: 'Cancel', result: false },
        { label: 'OK', cls: 'button-primary', result: true }
      ]
    }),
    alert: (message, title = '') => open({
      title, message,
      buttons: [{ label: 'OK', cls: 'button-primary', result: true }]
    })
  };
})();

document.addEventListener('DOMContentLoaded', function () {
  // Country search functionality
  const searchInput = document.getElementById('country-search');
  const countryItems = document.querySelectorAll('.country-item');

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const searchTerm = this.value.toLowerCase();
      countryItems.forEach(function (item) {
        const countryName = item.dataset.name;
        const countryLongName = item.dataset.longName;
        const countryCode = item.dataset.code;
        if (
          countryName.includes(searchTerm) ||
          countryCode.includes(searchTerm) ||
          countryLongName.includes(searchTerm)
        ) {
          item.style.display = 'inline-block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  }

  // GeoIP test functionality
  const testButton = document.getElementById('test-geoip');
  const resultDiv = document.getElementById('geoip-test-result');

  if (testButton && resultDiv) {
    testButton.addEventListener('click', function (e) {
      e.preventDefault();
      testButton.disabled = true;
      resultDiv.textContent = 'Testing...';
      resultDiv.style.display = 'block';

      fetch(fvCountryBlocker.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'fv_country_blocker_test_geoip',
          nonce: fvCountryBlocker.nonce
        })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            resultDiv.textContent = 'Test successful. Your country: ' + data.data.country;
          } else {
            resultDiv.textContent = 'Test failed: ' + data.data.message;
          }
        })
        .catch(() => {
          resultDiv.textContent = 'An error occurred while testing.';
        })
        .finally(() => {
          testButton.disabled = false;
        });
    });
  }
});

document.addEventListener('DOMContentLoaded', function () {
  var tabs = document.querySelectorAll('.nav-tab');
  var contents = document.querySelectorAll('.tab-content');

  function activate(target) {
    tabs.forEach(function (t) {
      t.classList.toggle('nav-tab-active', t.getAttribute('href') === '#' + target);
    });
    contents.forEach(function (content) {
      content.style.display = content.id === target ? 'block' : 'none';
    });
  }

  var initial = document.querySelector('.nav-tab.nav-tab-active')?.getAttribute('href')?.substring(1);
  if (initial) activate(initial);

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      activate(this.getAttribute('href').substring(1));
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const btn = document.querySelector('button.do-test-ip');

  btn.addEventListener('click', async e => {
    e.preventDefault();
    try {
      e.target.disabled = true;

      const response = await fetch(fvCountryBlocker.ajax_url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'fv_country_blocker_test_ip',
          nonce: fvCountryBlocker.nonce,
          ip: document.querySelector('input[name="test-ip"]').value
        })
      });

      const capitalize = s =>
        s
          .split(' ')
          .map(w => w.charAt(0).toUpperCase() + w.slice(1))
          .join(' ');

      const data = await response.json();
      const result = document.querySelector('.test-ip-result');
      result.innerHTML = '';
      if (!data.success) {
        result.textContent = `Failed: ${data.data}`;
        return;
      }
      const d = data.data;
      const rows = [];

      if (d.country) {
        const country = document.querySelector(`label.country-item[data-code='${d.country.toLowerCase()}']`);
        const flag = country ? country.querySelector('img').outerHTML : '';
        const name = country ? capitalize(country.dataset.longName) : d.country;
        const blockedTag = d.country_blocked ? ' <span style="color:#a00;font-weight:bold">(blocked country)</span>' : '';
        rows.push(`<div style="display:flex;align-items:center;gap:8px;margin:4px 0">${flag}<span>${name}</span>${blockedTag}</div>`);
      } else {
        rows.push('<div style="margin:4px 0"><em>Country: unknown</em></div>');
      }

      const yn = b => (b ? '<span style="color:#a00;font-weight:bold">YES</span>' : 'no');
      rows.push(`<div style="margin:4px 0">Tor exit: ${yn(d.is_tor)}</div>`);
      const dcLabel = d.is_apple_private_relay
        ? `${yn(d.is_datacenter)} <span style="color:#137333">(Apple iCloud Private Relay — exempted)</span>`
        : yn(d.is_datacenter);
      rows.push(`<div style="margin:4px 0">Datacenter / VPN: ${dcLabel}</div>`);
      rows.push(
        `<div style="margin:8px 0 0;padding:6px 10px;border-radius:4px;background:${d.would_block ? '#fbeaea' : '#eaf6ea'};color:${d.would_block ? '#a00' : '#137333'};font-weight:bold">` +
          (d.would_block ? 'This IP would be BLOCKED (403) site-wide.' : 'This IP would be allowed.') +
          '</div>'
      );

      result.innerHTML = rows.join('');
    } finally {
      e.target.disabled = false;
    }
  });
});

// -----------------------------------------------------------------------------
// Bypass tokens tab
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  const tab = document.getElementById('bypass-tokens');
  if (!tab) return;

  const tbody = tab.querySelector('table.fvcb-tokens tbody');
  const status = tab.querySelector('.fvcb-token-status');
  const nameInput = tab.querySelector('#fvcb-token-name');
  const createBtn = tab.querySelector('#fvcb-token-create');

  const esc = v => {
    if (v === null || v === undefined) return '';
    return String(v).replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  };

  const post = async (action, body = {}) => {
    const r = await fetch(fvCountryBlocker.ajax_url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action, nonce: fvCountryBlocker.nonce, ...body })
    });
    return r.json();
  };

  const sampleUrl = token => `${location.origin}/?fv_bypass=${encodeURIComponent(token)}`;

  const render = tokens => {
    if (!tokens.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="color:#888;">No tokens yet — create one above.</td></tr>';
      return;
    }
    tbody.innerHTML = tokens.map(t => {
      const revoked = String(t.revoked) === '1' || t.revoked === true;
      const tokenCell = revoked
        ? `<code style="color:#999;text-decoration:line-through">${esc(t.token)}</code>`
        : `<code class="fvcb-token-value" style="user-select:all">${esc(t.token)}</code>
           <button type="button" class="button button-small fvcb-copy" data-url="${esc(sampleUrl(t.token))}">Copy link</button>`;
      const status = revoked
        ? '<span style="color:#a00;font-weight:bold">revoked</span>'
        : '<span style="color:#137333;font-weight:bold">active</span>';
      const action = revoked
        ? '—'
        : `<button type="button" class="button button-small fvcb-revoke" data-id="${esc(t.id)}">Revoke</button>`;
      const url = revoked ? '—' : `<a href="${esc(sampleUrl(t.token))}" target="_blank" rel="noopener">link</a>`;
      return `<tr data-id="${esc(t.id)}">
        <td>${esc(t.name)}</td>
        <td>${tokenCell}</td>
        <td>${esc(t.created_at || '')}</td>
        <td>${esc(t.last_used_at || '—')}</td>
        <td>${status}</td>
        <td>${url}</td>
        <td>${action}</td>
      </tr>`;
    }).join('');
  };

  const refresh = async () => {
    const r = await post('fv_country_blocker_token_list');
    if (!r.success) { status.textContent = 'Load failed: ' + (r.data || ''); return; }
    render(r.data.tokens || []);
  };

  createBtn.addEventListener('click', async () => {
    const name = nameInput.value.trim();
    if (!name) { nameInput.focus(); return; }
    createBtn.disabled = true;
    status.textContent = 'Creating…';
    const r = await post('fv_country_blocker_token_create', { name });
    createBtn.disabled = false;
    if (!r.success) { status.textContent = 'Failed: ' + (r.data || ''); return; }
    status.textContent = 'Created.';
    nameInput.value = '';
    refresh();
  });

  tbody.addEventListener('click', async e => {
    const revokeBtn = e.target.closest('.fvcb-revoke');
    const copyBtn = e.target.closest('.fvcb-copy');
    if (revokeBtn) {
      const ok = await fvcbDialog.confirm(
        'Existing cookies using this token will stop working.',
        'Revoke token?'
      );
      if (!ok) return;
      const id = revokeBtn.dataset.id;
      revokeBtn.disabled = true;
      const r = await post('fv_country_blocker_token_revoke', { id });
      if (!r.success) {
        await fvcbDialog.alert('Revoke failed: ' + (r.data || ''));
        revokeBtn.disabled = false;
        return;
      }
      refresh();
    } else if (copyBtn) {
      try {
        await navigator.clipboard.writeText(copyBtn.dataset.url);
        copyBtn.textContent = 'Copied';
        setTimeout(() => { copyBtn.textContent = 'Copy link'; }, 1500);
      } catch (_) {}
    }
  });

  refresh();
});

// -----------------------------------------------------------------------------
// MaxMind license key <-> custom MMDB path are mutually exclusive: when one
// has a value, the other is disabled + visually muted. Sync on load + on input.
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  const license = document.querySelector('.fvcb-mmdb-license');
  const path = document.querySelector('.fvcb-mmdb-path');
  if (!license || !path) return;

  const sync = () => {
    const licenseHas = license.value.trim().length > 0;
    const pathHas = path.value.trim().length > 0;
    path.disabled = licenseHas;
    license.disabled = pathHas;
    path.style.opacity = licenseHas ? '0.5' : '';
    license.style.opacity = pathHas ? '0.5' : '';
    path.title = licenseHas ? 'Disabled — clear the License Key to use this' : '';
    license.title = pathHas ? 'Disabled — clear the Custom Path to use this' : '';
  };
  license.addEventListener('input', sync);
  path.addEventListener('input', sync);
  sync();
});

// -----------------------------------------------------------------------------
// Toggle enabled (header)
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.querySelector('button.fvcb-toggle-enabled');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    const wasEnabled = btn.dataset.enabled === '1';
    if (wasEnabled) {
      const ok = await fvcbDialog.confirm(
        'All country, datacenter and Tor blocking will stop site-wide.',
        'Disable blocking?'
      );
      if (!ok) return;
    }
    btn.disabled = true;
    try {
      const r = await fetch(fvCountryBlocker.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'fv_country_blocker_toggle_enabled',
          nonce: fvCountryBlocker.nonce
        })
      });
      const data = await r.json();
      if (!data.success) {
        await fvcbDialog.alert('Toggle failed: ' + (data.data || ''));
        btn.disabled = false;
        return;
      }
      // Reload so the admin-bar shield color refreshes server-side.
      location.reload();
    } catch (e) {
      await fvcbDialog.alert('Toggle error: ' + e.message);
      btn.disabled = false;
    }
  });
});

// -----------------------------------------------------------------------------
// Self-update button (header)
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.querySelector('button.fvcb-self-update');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    const target = btn.dataset.target;
    const ok = await fvcbDialog.confirm(
      `Plugin will be updated to <strong>v${target}</strong> from GitHub.`,
      'Update plugin?'
    );
    if (!ok) return;
    btn.disabled = true;
    const originalText = btn.textContent;
    btn.textContent = 'Updating…';
    try {
      const r = await fetch(fvCountryBlocker.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'fv_country_blocker_self_update',
          nonce: fvCountryBlocker.nonce
        })
      });
      const data = await r.json();
      if (!data.success) {
        await fvcbDialog.alert('Update failed: ' + (data.data?.error || JSON.stringify(data.data)));
        btn.disabled = false;
        btn.textContent = originalText;
        return;
      }
      btn.textContent = 'Updated, reloading…';
      // 1.5s delay gives PHP-FPM workers time to recompile the new files
      // (we triggered opcache_reset server-side). location.assign goes to
      // a clean settings URL so we don't reload mid-redirect-chain.
      setTimeout(() => location.assign(location.pathname + location.search), 1500);
    } catch (e) {
      await fvcbDialog.alert('Update error: ' + e.message);
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });
});
