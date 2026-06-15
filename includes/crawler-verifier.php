<?php
defined('ABSPATH') || exit;

/**
 * Verifies "known crawler" requests via forward-confirmed reverse DNS (FCrDNS).
 *
 * Only the major search engines that publish PTR records are supported:
 *   Googlebot -> *.googlebot.com / *.google.com
 *   bingbot   -> *.search.msn.com
 *   Slurp     -> *.crawl.yahoo.net      (Yahoo)
 *   Applebot  -> *.applebot.apple.com
 *
 * Social crawlers (DuckDuckBot, Twitterbot, facebookexternalhit) do NOT publish
 * reverse DNS and are intentionally out of scope — allow those via the manual
 * Trusted User-Agents list if desired.
 *
 * A request is exempt only if its User-Agent claims one of these crawlers AND
 * FCrDNS confirms it. DNS lookups run ONLY for crawler-claiming requests, and
 * results are cached per-IP, so ordinary visitor traffic is never touched.
 */
class FV_CrawlerVerifier {
  const OPT_ENABLE = 'fv_country_blocker_allow_known_crawlers';
  const CACHE_PREFIX = 'fvcb_crawler_';
  const TTL_VERIFIED = 7 * DAY_IN_SECONDS;
  const TTL_FAILED = HOUR_IN_SECONDS;

  // UA substring (case-insensitive) => allowed PTR hostname suffixes.
  private static function signatures() {
    return [
      'Googlebot' => ['.googlebot.com', '.google.com'],
      'bingbot'   => ['.search.msn.com'],
      'Slurp'     => ['.crawl.yahoo.net'],
      'Applebot'  => ['.applebot.apple.com'],
    ];
  }

  public static function is_enabled() {
    return get_option(self::OPT_ENABLE, '1') === '1';
  }

  /**
   * UA tokens this verifier governs. The trusted-UA matcher suppresses plain
   * substring matches for these (when enabled) so a spoofed UA can't bypass
   * the rDNS gate.
   */
  public static function governed_tokens() {
    return array_keys(self::signatures());
  }

  /** Which crawler does this UA claim to be? Returns allowed suffixes or null. No DNS. */
  private static function claimed_domains($ua) {
    if ($ua === '') return null;
    foreach (self::signatures() as $token => $domains) {
      if (stripos($ua, $token) !== false) return $domains;
    }
    return null;
  }

  /**
   * True if $ua claims a supported crawler AND FCrDNS confirms $ip. Cached per IP.
   * Returns false immediately (no DNS) when disabled or when the UA isn't claiming
   * a supported crawler.
   */
  public static function is_verified_crawler($ip, $ua) {
    if (!self::is_enabled()) return false;
    if (empty($ip) || empty($ua)) return false;
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return false;

    $domains = self::claimed_domains($ua);
    if ($domains === null) return false; // not claiming a supported crawler -> no DNS

    $cache_key = self::CACHE_PREFIX . md5($ip);
    $cached = get_transient($cache_key);
    if ($cached === '1') return true;
    if ($cached === '0') return false;

    $ok = self::fcrdns($ip, $domains);
    set_transient($cache_key, $ok ? '1' : '0', $ok ? self::TTL_VERIFIED : self::TTL_FAILED);
    return $ok;
  }

  /**
   * Forward-confirmed reverse DNS:
   *   1. PTR lookup on the IP -> hostname
   *   2. hostname must end with an allowed domain suffix (on a dot boundary)
   *   3. forward-resolve hostname -> must include the original IP
   */
  private static function fcrdns($ip, $domains) {
    $host = @gethostbyaddr($ip);
    if (!$host || $host === $ip) return false;

    $host_l = strtolower(rtrim($host, '.'));
    $suffix_ok = false;
    foreach ($domains as $d) {
      $d = strtolower($d);
      if (strlen($host_l) >= strlen($d) && substr($host_l, -strlen($d)) === $d) {
        $suffix_ok = true;
        break;
      }
    }
    if (!$suffix_ok) return false;

    return self::forward_has_ip($host, $ip);
  }

  private static function forward_has_ip($host, $ip) {
    $is_v6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;

    if (function_exists('dns_get_record')) {
      $recs = @dns_get_record($host, $is_v6 ? DNS_AAAA : DNS_A);
      if (is_array($recs) && !empty($recs)) {
        foreach ($recs as $r) {
          if ($is_v6) {
            if (isset($r['ipv6']) && self::same_ip($r['ipv6'], $ip)) return true;
          } else {
            if (isset($r['ip']) && $r['ip'] === $ip) return true;
          }
        }
        return false; // host resolves, but not to this IP
      }
    }

    // Fallback (IPv4 only): gethostbyname returns a single A record.
    if (!$is_v6) {
      $resolved = @gethostbyname($host);
      return $resolved === $ip;
    }
    return false;
  }

  private static function same_ip($a, $b) {
    $na = @inet_pton($a);
    $nb = @inet_pton($b);
    return $na !== false && $na === $nb;
  }
}
