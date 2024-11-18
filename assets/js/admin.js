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

      const data = await response.json();
      const result = document.querySelector('.test-ip-result');
      if (data.success) {
        result.textContent = data.country;
      } else {
        result.textContent = data.data;
      }
    } finally {
      e.target.disabled = false;
    }
  });
});
