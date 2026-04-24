<?php
defined('ABSPATH') || exit;

class FV_Captcha {
  const OPTION_SECRET = 'fv_captcha_secret';
  const MIN_DWELL_SECONDS = 3;
  const MIN_MOVES = 1;
  const MIN_INTERACTIONS = 1;

  private static $script_printed = false;

  public static function render() {
    $a = mt_rand(1, 9);
    $b = mt_rand(1, 9);
    $answer = $a + $b;
    $nonce = bin2hex(random_bytes(8));
    $token = self::issue_token($answer, $nonce);
    $img = self::image_data_url("$a + $b = ?");

    $input_style = 'width:90px;padding:8px 10px;border:1px solid #ccc;border-radius:4px;font-size:16px;text-align:center;background:#fff;color:#111;box-sizing:border-box';
    $html = '<div class="fv-captcha" style="display:inline-flex;align-items:center;gap:10px">';
    $html .= '<img src="' . esc_attr($img) . '" alt="captcha" class="fv-captcha-image" style="border:1px solid #e0e0e0;border-radius:4px;display:block" />';
    $html .= '<input type="text" name="fv_captcha_answer" inputmode="numeric" pattern="[0-9]*" autocomplete="off" required style="' . esc_attr($input_style) . '" />';
    $html .= '<input type="hidden" name="fv_captcha_token" value="' . esc_attr($token) . '" />';
    $html .= '<input type="hidden" name="fv_captcha_moves" value="0" />';
    $html .= '<input type="hidden" name="fv_captcha_interactions" value="0" />';
    $html .= '</div>';

    echo $html;
    self::print_script_once();
  }

  public static function verify_from_post() {
    return self::verify(
      $_POST['fv_captcha_token'] ?? '',
      $_POST['fv_captcha_answer'] ?? '',
      (int) ($_POST['fv_captcha_moves'] ?? 0),
      (int) ($_POST['fv_captcha_interactions'] ?? 0)
    );
  }

  public static function verify($token, $answer, $moves, $interactions) {
    $payload = self::decode_token($token);
    if (!$payload) {
      return ['ok' => false, 'reason' => 'bad_token'];
    }
    if ((int) $answer !== (int) $payload['a']) {
      return ['ok' => false, 'reason' => 'wrong_answer'];
    }
    $dwell = time() - (int) $payload['t'];
    if ($dwell < self::MIN_DWELL_SECONDS) {
      return ['ok' => false, 'reason' => 'too_fast', 'dwell' => $dwell];
    }
    if ($moves < self::MIN_MOVES) {
      return ['ok' => false, 'reason' => 'no_mouse', 'moves' => $moves];
    }
    if ($interactions < self::MIN_INTERACTIONS) {
      return ['ok' => false, 'reason' => 'no_interactions', 'interactions' => $interactions];
    }
    return ['ok' => true, 'dwell' => $dwell];
  }

  private static function secret() {
    $s = get_option(self::OPTION_SECRET);
    if (!$s) {
      $s = bin2hex(random_bytes(32));
      update_option(self::OPTION_SECRET, $s, false);
    }
    return $s;
  }

  private static function issue_token($answer, $nonce) {
    $payload = ['a' => (int) $answer, 'n' => $nonce, 't' => time()];
    $json = json_encode($payload);
    $sig = hash_hmac('sha256', $json, self::secret());
    return rtrim(strtr(base64_encode($json), '+/', '-_'), '=') . '.' . $sig;
  }

  private static function decode_token($token) {
    if (!is_string($token) || strpos($token, '.') === false) {
      return null;
    }
    [$b64, $sig] = explode('.', $token, 2);
    $json = base64_decode(strtr($b64, '-_', '+/'), true);
    if ($json === false) {
      return null;
    }
    $expected = hash_hmac('sha256', $json, self::secret());
    if (!hash_equals($expected, $sig)) {
      return null;
    }
    $payload = json_decode($json, true);
    if (!is_array($payload) || !isset($payload['a'], $payload['t'])) {
      return null;
    }
    return $payload;
  }

  private static function image_data_url($text) {
    if (!function_exists('imagecreatetruecolor')) {
      return 'data:image/gif;base64,R0lGODlhAQABAAAAACwAAAAAAQABAAA=';
    }
    $w = 140;
    $h = 44;
    $im = imagecreatetruecolor($w, $h);
    $bg = imagecolorallocate($im, 245, 245, 248);
    imagefilledrectangle($im, 0, 0, $w, $h, $bg);
    $noise = imagecolorallocate($im, 200, 200, 210);
    for ($i = 0; $i < 140; $i++) {
      imagesetpixel($im, mt_rand(0, $w - 1), mt_rand(0, $h - 1), $noise);
    }
    for ($i = 0; $i < 3; $i++) {
      imageline($im, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $noise);
    }
    $text_color = imagecolorallocate($im, 40, 50, 90);
    $font = 5;
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);
    imagestring($im, $font, max(4, intval(($w - $tw) / 2)), max(2, intval(($h - $th) / 2)), $text, $text_color);
    ob_start();
    imagepng($im);
    $png = ob_get_clean();
    imagedestroy($im);
    return 'data:image/png;base64,' . base64_encode($png);
  }

  private static function print_script_once() {
    if (self::$script_printed) {
      return;
    }
    self::$script_printed = true;
    ?>
    <script>
    (function(){
      var moves = 0, inters = 0, pending = false;
      function flush(){
        pending = false;
        document.querySelectorAll('.fv-captcha').forEach(function(c){
          var m = c.querySelector('[name="fv_captcha_moves"]');
          var i = c.querySelector('[name="fv_captcha_interactions"]');
          if (m) m.value = moves;
          if (i) i.value = inters;
        });
      }
      function schedule(){ if (!pending){ pending = true; (window.requestAnimationFrame || setTimeout)(flush, 16); } }
      document.addEventListener('mousemove', function(){ moves++; schedule(); }, {passive:true});
      document.addEventListener('touchmove', function(){ moves++; schedule(); }, {passive:true});
      ['click','keydown','focus'].forEach(function(ev){
        document.addEventListener(ev, function(){ inters++; schedule(); }, {passive:true, capture:true});
      });
    })();
    </script>
    <?php
  }
}
