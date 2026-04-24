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

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      var target = this.getAttribute('href').substring(1);

      tabs.forEach(function (t) {
        t.classList.remove('nav-tab-active');
      });
      this.classList.add('nav-tab-active');

      contents.forEach(function (content) {
        content.style.display = content.id === target ? 'block' : 'none';
      });
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
      rows.push(`<div style="margin:4px 0">Datacenter / VPN: ${yn(d.is_datacenter)}</div>`);
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
