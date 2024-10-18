=== FV Country Blocker ===
Contributors: daberelay
Tags: country blocker, IP blocker, geo-blocking, security
Requires at least: 5.0
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 1.0.28
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple & lean yet powerful plugin for blocking unwanted traffic from specific countries using MaxMind's IP database.

== Description ==

FV Country Blocker is a simple & lean, yet strong way of blocking unwanted traffic from accessing your website based on their geographic location. By utilizing the MaxMind IP database, this plugin allows you to block visitors from specific countries, keeping your website safe from unwanted traffic.

**Development Testing**:
To test in development or staging environments, you can simulate access from different countries by adding a query parameter `[? or &]force_country_ip=xxx.xxx.xxx.xxx` to the URL, replacing `xxx.xxx.xxx.xxx` with the IP address you want to test.

== Installation ==

1. Install via the Add New Plugin wordpress interface, or upload the `fv-country-blocker` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the plugin settings under **Settings > FV Country Blocker** to:
   a. configure which countries to block.
   b. set the MaxMind API key, or a custom path of the geolite2 country db.
   **Bear in mind that this plugin will only download the DB regularly if the API key is provided, and the custom path is empty.**

== Frequently Asked Questions ==

= How accurate is the country blocking? =

FV Country Blocker relies on MaxMind's IP database, which claims it to be 99.8% accurate. However, false positives can still occur, so you should regularly monitor blocked traffic to ensure legitimate users aren't affected.

= Will blocking certain countries affect my SEO? =

Yes, blocking countries can impact SEO, especially if search engine bots are located in IP ranges that belong to the blocked country. Make sure to whitelist major search engines to avoid SEO issues.

= How can I test the plugin on a staging or development site? =

You can simulate a visitor's country by adding `?force_country_ip=xxx.xxx.xxx.xxx` to the request URL. Replace `xxx.xxx.xxx.xxx` with the IP address you want to test from.

== Screenshots ==

1. **Settings Page** – Easily block or allow traffic from specific countries.

== Changelog ==

= 1.0.0 =

- Initial release of the plugin.
- Country blocking based on MaxMind IP database.
- Added development testing feature using query parameters.

== Upgrade Notice ==

= 1.0.0 =
Initial release. Ensure that you configure country blocking properly to avoid inadvertently blocking search engine bots or legitimate users.

== License ==

This plugin is licensed under the GPLv2 or later. See https://www.gnu.org/licenses/gpl-2.0.html for more information.
