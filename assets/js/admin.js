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
